<?php

class AdminOfferController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->currentCustomer()) {
            $_SESSION['flash'] = 'Please log in with an administrator account to manage offers.';
            redirect('/login');
        }

        if (!$this->hasRole('admin')) {
            $_SESSION['flash'] = 'Offer management is restricted to administrators.';
            redirect('/crm');
        }
    }

    public function index(): void
    {
        $offers = (new Offer($this->db))->all();
        $counts = ['active' => 0, 'expired' => 0, 'archived' => 0];
        foreach ($offers as $offer) {
            $counts[$this->status($offer)]++;
        }

        $this->view('crm/offers', [
            'title' => 'Manage offers',
            'offers' => $offers,
            'counts' => $counts,
        ]);
    }

    public function add(): void
    {
        $this->renderEditor([
            'id' => 0,
            'code' => '',
            'description' => '',
            'discount_percent' => 10,
            'expiry_date' => '',
            'is_active' => 1,
        ]);
    }

    public function edit(): void
    {
        $offer = (new Offer($this->db))->find((int) $this->input('id'));
        if (!$offer) {
            $_SESSION['flash'] = 'The requested offer could not be found.';
            redirect('/crm/offers');
        }

        $this->renderEditor($offer);
    }

    public function save(): void
    {
        $this->rejectInvalidCsrf('/crm/offers');

        $model = new Offer($this->db);
        $id = (int) $this->input('id');
        $existing = $id > 0 ? $model->find($id) : null;
        if ($id > 0 && !$existing) {
            $_SESSION['flash'] = 'The requested offer could not be found.';
            redirect('/crm/offers');
        }

        $code = strtoupper($this->input('code'));
        $description = $this->input('description');
        $discountRaw = $this->input('discount_percent');
        $expiryDate = $this->input('expiry_date');
        $errors = [];

        if (!preg_match('/^[A-Z0-9][A-Z0-9-]{2,19}$/', $code)) {
            $errors[] = 'Offer code must contain 3–20 letters, numbers or hyphens, starting with a letter or number.';
        } elseif ($model->codeExists($code, $id)) {
            $errors[] = 'That offer code is already in use. Enter a unique code.';
        }

        if (strlen($description) < 5 || strlen($description) > 255) {
            $errors[] = 'Description must contain between 5 and 255 characters.';
        }

        $discountPercent = filter_var(
            $discountRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 90]]
        );
        if ($discountPercent === false) {
            $errors[] = 'Discount must be a whole percentage between 1 and 90.';
        }

        if ($expiryDate !== '') {
            $parsedExpiry = DateTimeImmutable::createFromFormat('!Y-m-d', $expiryDate);
            if (!$parsedExpiry || $parsedExpiry->format('Y-m-d') !== $expiryDate) {
                $errors[] = 'Enter a valid expiry date.';
            } elseif ($expiryDate < date('Y-m-d')) {
                $errors[] = 'Expiry date cannot be in the past. Choose today or a future date.';
            }
        }

        $offer = [
            'id' => $id,
            'code' => $code,
            'description' => $description,
            'discount_percent' => $discountPercent === false ? $discountRaw : $discountPercent,
            'expiry_date' => $expiryDate,
            'is_active' => (int) ($existing['is_active'] ?? 1),
        ];

        if ($errors) {
            $this->renderEditor($offer, $errors);
            return;
        }

        $attributes = [
            'code' => $code,
            'description' => $description,
            'discount_percent' => (int) $discountPercent,
            'expiry_date' => $expiryDate === '' ? null : $expiryDate,
            'is_active' => 1,
        ];

        try {
            if ($id > 0) {
                $model->update($id, $attributes);
                $event = 'offer.updated';
                $message = 'Offer ' . $code . ' was updated.';
            } else {
                $id = $model->create($attributes);
                $event = 'offer.created';
                $message = 'Offer ' . $code . ' was created and is now available to customers.';
            }
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
            $errors[] = 'That offer code is already in use. Enter a unique code.';
            $this->renderEditor($offer, $errors);
            return;
        }

        (new AuditLog($this->db))->record(
            (int) $this->currentCustomer()['id'],
            $event,
            'offer',
            $id,
            'Success',
            json_encode([
                'code' => $code,
                'discount_percent' => (int) $discountPercent,
                'expiry_date' => $expiryDate === '' ? null : $expiryDate,
            ], JSON_UNESCAPED_SLASHES)
        );

        $_SESSION['flash'] = $message;
        redirect('/crm/offers');
    }

    public function archive(): void
    {
        $this->rejectInvalidCsrf('/crm/offers');
        $model = new Offer($this->db);
        $offer = $model->find((int) $this->input('id'));
        if (!$offer) {
            $_SESSION['flash'] = 'The requested offer could not be found.';
            redirect('/crm/offers');
        }

        $model->setActive((int) $offer['id'], false);
        $this->auditStatusChange($offer, 'offer.archived');
        $_SESSION['flash'] = 'Offer ' . $offer['code'] . ' was archived. It is no longer visible or redeemable.';
        redirect('/crm/offers');
    }

    public function reactivate(): void
    {
        $this->rejectInvalidCsrf('/crm/offers');
        $model = new Offer($this->db);
        $offer = $model->find((int) $this->input('id'));
        if (!$offer) {
            $_SESSION['flash'] = 'The requested offer could not be found.';
            redirect('/crm/offers');
        }

        if (!empty($offer['expiry_date']) && $offer['expiry_date'] < date('Y-m-d')) {
            $_SESSION['flash'] = 'Update the expired date before reactivating offer ' . $offer['code'] . '.';
            redirect('/crm/offer/edit?id=' . (int) $offer['id']);
        }

        $model->setActive((int) $offer['id'], true);
        $this->auditStatusChange($offer, 'offer.reactivated');
        $_SESSION['flash'] = 'Offer ' . $offer['code'] . ' was reactivated and is available to customers.';
        redirect('/crm/offers');
    }

    private function renderEditor(array $offer, array $errors = []): void
    {
        $this->view('crm/offer-editor', [
            'title' => !empty($offer['id']) ? 'Edit offer' : 'Add offer',
            'offer' => $offer,
            'errors' => $errors,
            'offerStatus' => $this->status($offer),
        ]);
    }

    private function status(array $offer): string
    {
        if ((int) ($offer['is_active'] ?? 1) !== 1) {
            return 'archived';
        }
        if (!empty($offer['expiry_date']) && $offer['expiry_date'] < date('Y-m-d')) {
            return 'expired';
        }
        return 'active';
    }

    private function auditStatusChange(array $offer, string $event): void
    {
        (new AuditLog($this->db))->record(
            (int) $this->currentCustomer()['id'],
            $event,
            'offer',
            (int) $offer['id'],
            'Success',
            json_encode(['code' => $offer['code']], JSON_UNESCAPED_SLASHES)
        );
    }
}

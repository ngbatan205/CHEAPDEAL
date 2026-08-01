<?php

class CRMController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $role = $_SESSION['customer']['role'] ?? null;
        if (!in_array($role, ['admin', 'csr'], true)) {
            $_SESSION['flash'] = 'Please log in with an authorised staff account to access the operations workspace.';
            redirect('/login');
        }
    }

    public function dashboard(): void
    {
        $planChanges = (new AuditLog($this->db))->subscriptionChanges();
        $this->view('crm/dashboard', [
            'title' => 'CRM dashboard',
            'packages' => (new Package($this->db))->all(),
            'orders' => (new Order($this->db))->all(),
            'customers' => (new Customer($this->db))->all(),
            'enquiries' => (new Enquiry($this->db))->all(),
            'planChanges' => $planChanges,
        ]);
    }

    public function subscriptionChanges(): void
    {
        $query = $this->input('q');
        $this->view('crm/subscription-changes', [
            'title' => 'Plan-change activity',
            'changes' => (new AuditLog($this->db))->subscriptionChanges(null, $query),
            'query' => $query,
        ]);
    }

    public function packages(): void
    {
        $canManageCatalogue = $this->hasRole('admin');
        $this->view('crm/packages', [
            'title' => $canManageCatalogue ? 'Manage catalogue' : 'Catalogue reference',
            'packages' => (new Package($this->db))->all(null, '', $canManageCatalogue),
            'deals' => (new Deal($this->db))->all(null, $canManageCatalogue),
            // Avoid the generic $isAdmin name: the shared navbar uses it for
            // the broader admin-or-CSR staff navigation state.
            'canManageCatalogue' => $canManageCatalogue,
        ]);
    }

    public function addPackage(): void
    {
        $this->requireAdmin('/crm/packages');
        $kind = $this->input('kind', 'package');
        $kind = $kind === 'deal' ? 'deal' : 'package';
        $this->renderPackageEditor($kind);
    }

    public function editPackage(): void
    {
        $this->requireAdmin('/crm/packages');
        $kind = $this->input('kind') === 'deal' ? 'deal' : 'package';
        $id = (int) $this->input('id');
        $item = $kind === 'deal'
            ? (new Deal($this->db))->find($id, true)
            : (new Package($this->db))->find($id, true);

        if (!$item) {
            http_response_code(404);
        }

        $this->renderPackageEditor($kind, $item);
    }

    public function savePackage(): void
    {
        $this->requireAdmin('/crm/packages');
        $this->rejectInvalidCsrf('/crm/packages');
        $kind = $this->input('kind') === 'deal' ? 'deal' : 'package';
        $id = (int) $this->input('id');
        $errors = [];

        if ($kind === 'package') {
            $packageModel = new Package($this->db);
            $existingPackage = $id > 0 ? $packageModel->find($id, true) : null;
            $item = [
                'id' => $id,
                'package_name' => $this->input('name'),
                'category' => $this->input('category'),
                'price' => $this->input('price'),
                'minutes' => $this->input('minutes', '0'),
                'sms' => $this->input('sms', '0'),
                'data_gb' => $this->input('data_gb', '0'),
                'description' => $this->input('description'),
                'stock' => $this->input('stock', '0'),
                'image' => $existingPackage['image'] ?? '',
            ];

            if ($item['package_name'] === '') {
                $errors[] = 'Package name is required.';
            }
            if (!in_array($item['category'], ['Mobile', 'Broadband', 'Tablet'], true)) {
                $errors[] = 'Please choose a valid category.';
            }
            if (!is_numeric($item['price']) || (float) $item['price'] <= 0) {
                $errors[] = 'Price must be greater than zero.';
            }
            foreach (['minutes', 'sms', 'data_gb', 'stock'] as $numberField) {
                if (filter_var($item[$numberField], FILTER_VALIDATE_INT) === false || (int) $item[$numberField] < 0) {
                    $errors[] = ucfirst(str_replace('_', ' ', $numberField)) . ' must be zero or greater.';
                }
            }
            if ($item['description'] === '') {
                $errors[] = 'Description is required.';
            }

            if ($errors) {
                $this->renderPackageEditor($kind, $item, $errors);
                return;
            }

            $item['image'] = $this->storePackageImage($item['image'], $errors);
            if ($item['image'] === '') {
                $errors[] = 'Please choose an image for this package.';
            }
            if ($errors) {
                $this->renderPackageEditor($kind, $item, $errors);
                return;
            }

            $attributes = [
                'package_name' => $item['package_name'],
                'category' => $item['category'],
                'price' => round((float) $item['price'], 2),
                'minutes' => (int) $item['minutes'],
                'sms' => (int) $item['sms'],
                'data_gb' => (int) $item['data_gb'],
                'description' => $item['description'],
                'stock' => (int) $item['stock'],
                'image' => $item['image'],
            ];
            $savedId = $id > 0 ? $id : $packageModel->create($attributes);
            if ($id > 0) {
                $packageModel->update($id, $attributes);
            }
            (new AuditLog($this->db))->record(
                (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
                $id > 0 ? 'catalogue.package.updated' : 'catalogue.package.created',
                'package',
                $savedId,
                'Success',
                $item['package_name']
            );
            $_SESSION['flash'] = $id > 0 ? 'Package updated successfully.' : 'Package added successfully.';
            redirect('/crm/packages');
        }

        $packageIds = array_values(array_unique(array_filter(
            array_map('intval', (array) ($_POST['package_ids'] ?? []))
        )));
        $existingDeal = $id > 0 ? (new Deal($this->db))->find($id, true) : null;
        $item = [
            'id' => $id,
            'deal_name' => $this->input('name'),
            'deal_type' => $this->input('deal_type'),
            'description' => $this->input('description'),
            'stock' => $this->input('stock', '0'),
            'image' => $existingDeal['image'] ?? '',
            'packages' => array_map(
                fn (int $packageId): array => ['id' => $packageId],
                $packageIds
            ),
        ];
        $requiredCount = $item['deal_type'] === 'TriplePackage' ? 3 : 2;

        if ($item['deal_name'] === '') {
            $errors[] = 'Combo name is required.';
        }
        if (!in_array($item['deal_type'], ['DoublePackage', 'TriplePackage'], true)) {
            $errors[] = 'Please choose Double or Triple combo.';
        }
        if (filter_var($item['stock'], FILTER_VALIDATE_INT) === false || (int) $item['stock'] < 0) {
            $errors[] = 'Stock must be zero or greater.';
        }
        if ($item['description'] === '') {
            $errors[] = 'Description is required.';
        }

        $selectedPackages = [];
        $packageModel = new Package($this->db);
        foreach ($packageIds as $packageId) {
            $package = $packageModel->find($packageId);
            if ($package) {
                $selectedPackages[] = $package;
            }
        }
        if (count($selectedPackages) !== $requiredCount) {
            $errors[] = "A {$requiredCount}-service combo must contain exactly {$requiredCount} packages.";
        }
        if (count(array_unique(array_column($selectedPackages, 'category'))) !== count($selectedPackages)) {
            $errors[] = 'Every package in a combo must come from a different category.';
        }

        if ($errors) {
            $this->renderPackageEditor($kind, $item, $errors);
            return;
        }

        $item['image'] = $this->storePackageImage($item['image'], $errors);
        if ($errors) {
            $this->renderPackageEditor($kind, $item, $errors);
            return;
        }

        $normalPrice = round(array_sum(array_map(
            fn (array $package): float => (float) $package['price'],
            $selectedPackages
        )), 2);
        $attributes = [
            'deal_name' => $item['deal_name'],
            'deal_type' => $item['deal_type'],
            'normal_price' => $normalPrice,
            'price' => round($normalPrice * .85, 2),
            'description' => $item['description'],
            'stock' => (int) $item['stock'],
            'image' => $item['image'],
        ];

        try {
            $model = new Deal($this->db);
            if ($id > 0) {
                $model->update($id, $attributes, $packageIds);
                $savedId = $id;
            } else {
                $savedId = $model->create($attributes, $packageIds);
            }
            (new AuditLog($this->db))->record(
                (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
                $id > 0 ? 'catalogue.deal.updated' : 'catalogue.deal.created',
                'deal',
                $savedId,
                'Success',
                $item['deal_name']
            );
        } catch (PDOException $exception) {
            $this->renderPackageEditor($kind, $item, ['The combo name is already in use or the data is invalid.']);
            return;
        }

        $_SESSION['flash'] = $id > 0 ? 'Combo updated successfully.' : 'Combo added with a 15% discount.';
        redirect('/crm/packages');
    }

    public function deletePackage(): void
    {
        $this->requireAdmin('/crm/packages');
        $this->rejectInvalidCsrf('/crm/packages');
        $kind = $this->input('kind') === 'deal' ? 'deal' : 'package';
        $id = (int) $this->input('id');

        if ($kind === 'deal') {
            $model = new Deal($this->db);
            $model->archive($id);
            (new AuditLog($this->db))->record(
                (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
                'catalogue.deal.archived', 'deal', $id
            );
            $_SESSION['flash'] = 'Combo archived. Historical records are retained and it can be reactivated from CRM catalogue.';
            redirect('/crm/packages');
        }

        $model = new Package($this->db);
        $model->archive($id);
        (new AuditLog($this->db))->record(
            (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
            'catalogue.package.archived', 'package', $id
        );
        $_SESSION['flash'] = 'Package archived. It is hidden from new orders, retained for history and can be reactivated.';
        redirect('/crm/packages');
    }

    public function reactivatePackage(): void
    {
        $this->requireAdmin('/crm/packages');
        $this->rejectInvalidCsrf('/crm/packages');
        $kind = $this->input('kind') === 'deal' ? 'deal' : 'package';
        $id = (int) $this->input('id');
        if ($kind === 'deal') {
            (new Deal($this->db))->reactivate($id);
        } else {
            (new Package($this->db))->reactivate($id);
        }
        (new AuditLog($this->db))->record(
            (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
            'catalogue.' . $kind . '.reactivated', $kind, $id
        );
        $_SESSION['flash'] = ucfirst($kind) . ' reactivated and available in the customer catalogue and CSR reference view.';
        redirect('/crm/packages');
    }

    private function renderPackageEditor(string $kind, ?array $item = null, array $errors = []): void
    {
        $this->view('crm/package-editor', [
            'title' => ($item ? 'Edit ' : 'Add ') . ($kind === 'deal' ? 'combo' : 'package'),
            'kind' => $kind,
            'item' => $item,
            'errors' => $errors,
            'availablePackages' => (new Package($this->db))->all(),
        ]);
    }

    private function storePackageImage(string $currentImage, array &$errors): string
    {
        $upload = $_FILES['image_file'] ?? null;
        if (!$upload || (int) $upload['error'] === UPLOAD_ERR_NO_FILE) {
            return $currentImage;
        }

        if ((int) $upload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The image could not be uploaded. Please try again.';
            return $currentImage;
        }
        if ((int) $upload['size'] > 5 * 1024 * 1024) {
            $errors[] = 'The image must be 5MB or smaller.';
            return $currentImage;
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        if (!isset($allowedTypes[$mimeType])) {
            $errors[] = 'Please upload a JPG, PNG, WEBP or GIF image.';
            return $currentImage;
        }

        $uploadDirectory = dirname(APP_ROOT) . '/public/images/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            $errors[] = 'The image upload folder could not be created.';
            return $currentImage;
        }

        $fileName = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $allowedTypes[$mimeType];
        $destination = $uploadDirectory . '/' . $fileName;
        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
            $errors[] = 'The image could not be saved.';
            return $currentImage;
        }

        return 'images/uploads/' . $fileName;
    }

    public function orders(): void
    {
        $this->view('crm/orders', ['title' => 'Orders', 'orders' => (new Order($this->db))->all()]);
    }

    public function customers(): void
    {
        $query = $this->input('q');
        $this->view('crm/customers', [
            'title' => 'Customers',
            'customers' => (new Customer($this->db))->search($query),
            'query' => $query,
            'errors' => $_SESSION['crm_customer_errors'] ?? [],
            'old' => $_SESSION['crm_customer_old'] ?? [],
        ]);
        unset($_SESSION['crm_customer_errors'], $_SESSION['crm_customer_old']);
    }

    public function createCustomer(): void
    {
        $old = [
            'full_name' => $this->input('full_name'),
            'email' => strtolower($this->input('email')),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
        ];
        $password = $this->input('password');
        $model = new Customer($this->db);
        $errors = [];
        if (mb_strlen($old['full_name']) < 2) $errors['full_name'] = 'Enter the customer full name.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($model->findByEmail($old['email'])) {
            $errors['email'] = 'That email already belongs to an account.';
        }
        if (!preg_match('/^[0-9+()\s-]{8,20}$/', $old['phone'])) {
            $errors['phone'] = 'Enter a valid telephone number.';
        } elseif ($model->findByPhone($old['phone'])) {
            $errors['phone'] = 'That telephone number already belongs to an account.';
        }
        if (mb_strlen($old['address']) < 5) $errors['address'] = 'Enter the customer address.';
        if (strlen($password) < 8) $errors['password'] = 'Temporary password must contain at least 8 characters.';

        if ($errors) {
            $_SESSION['crm_customer_errors'] = $errors;
            $_SESSION['crm_customer_old'] = $old;
            redirect('/crm/customers');
        }

        $customer = $model->create([
            ...$old,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);
        (new AuditLog($this->db))->record(
            (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
            'crm.customer.created', 'customer', (int) $customer['id']
        );
        $_SESSION['flash'] = 'Customer account created successfully.';
        redirect('/crm/customer?id=' . (int) $customer['id']);
    }

    public function records(): void
    {
        $this->requireAdmin('/crm');
        $this->view('crm/records', [
            'title' => 'Operational records',
            'records' => (new OperationalRecord($this->db))->all(
                $this->input('type'),
                $this->input('status'),
                $this->input('q')
            ),
            'selectedType' => $this->input('type'),
            'selectedStatus' => $this->input('status'),
            'query' => $this->input('q'),
        ]);
    }

    public function recordDetail(): void
    {
        $this->requireAdmin('/crm');
        $record = (new OperationalRecord($this->db))->find(
            $this->input('type'),
            (int) $this->input('id')
        );
        if (!$record) {
            http_response_code(404);
        }
        $this->view('crm/record-detail', [
            'title' => $record ? $record['reference'] : 'Record not found',
            'record' => $record,
        ]);
    }

    private function requireAdmin(string $returnPath): void
    {
        if ($this->hasRole('admin')) {
            return;
        }
        $_SESSION['flash'] = 'This action is restricted to administrators. CSR access is read-only.';
        redirect($returnPath);
    }

    public function telephoneOrder(): void
    {
        $this->renderTelephoneOrder();
    }

    public function verifyTelephoneCustomer(): void
    {
        $phone = $this->input('phone');
        $errors = [];

        if ($phone === '') {
            $errors[] = 'Enter the caller telephone number.';
        }

        $customer = $errors
            ? null
            : (new Customer($this->db))->findByPhone($phone);

        if (!$errors && (!$customer || ($customer['role'] ?? '') !== 'customer')) {
            $errors[] = 'No customer account was found with that telephone number.';
        }

        if ($errors) {
            $this->renderTelephoneOrder($errors, [
                'mode' => 'existing',
                'existing_phone' => $phone,
            ]);
            return;
        }

        $_SESSION['telephone_order_customer_id'] = (int) $customer['id'];
        $_SESSION['flash'] = 'Caller verified successfully. You can now place the telephone order.';
        redirect('/crm/telephone-order');
    }

    public function createTelephoneCustomer(): void
    {
        $old = [
            'mode' => 'new',
            'full_name' => $this->input('full_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'address' => $this->input('address'),
        ];
        $password = $this->input('password');
        $errors = [];
        $customerModel = new Customer($this->db);

        if (mb_strlen($old['full_name']) < 2) {
            $errors[] = 'Enter the caller full name.';
        }
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } elseif ($customerModel->findByEmail($old['email'])) {
            $errors[] = 'That email already belongs to an account. Use Existing customer verification.';
        }
        if (!preg_match('/^[0-9+()\s-]{8,20}$/', $old['phone'])) {
            $errors[] = 'Enter a valid telephone number containing 8 to 20 characters.';
        } elseif ($customerModel->findByPhone($old['phone'])) {
            $errors[] = 'That telephone number already belongs to an account. Verify the existing customer instead.';
        }
        if (mb_strlen($old['address']) < 5) {
            $errors[] = 'Enter the customer address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'The temporary password must contain at least 8 characters.';
        }

        if ($errors) {
            $this->renderTelephoneOrder($errors, $old);
            return;
        }

        $customer = $customerModel->create([
            'full_name' => $old['full_name'],
            'email' => $old['email'],
            'phone' => $old['phone'],
            'address' => $old['address'],
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);

        $_SESSION['telephone_order_customer_id'] = (int) $customer['id'];
        $_SESSION['flash'] = 'Customer account created and caller details validated.';
        redirect('/crm/telephone-order');
    }

    public function placeTelephoneOrder(): void
    {
        $customerId = (int) ($_SESSION['telephone_order_customer_id'] ?? 0);
        $customer = (new Customer($this->db))->find($customerId);
        $packageId = (int) $this->input('package_id');
        $quantityInput = $this->input('quantity');
        $quantity = filter_var($quantityInput, FILTER_VALIDATE_INT);
        $package = (new Package($this->db))->find($packageId);
        $errors = [];

        if (!$customer || ($customer['role'] ?? '') !== 'customer') {
            unset($_SESSION['telephone_order_customer_id']);
            $errors[] = 'Verify the caller before placing a telephone order.';
        }
        if (!$package) {
            $errors[] = 'Select an available package.';
        }
        if ($quantity === false || $quantity < 1 || $quantity > 99) {
            $errors[] = 'Quantity must be a whole number between 1 and 99.';
        } elseif ($package && $quantity > (int) $package['stock']) {
            $errors[] = 'The selected quantity is greater than the available stock.';
        }

        if ($errors) {
            $this->renderTelephoneOrder($errors, [
                'mode' => 'order',
                'package_id' => (string) $packageId,
                'quantity' => $quantityInput,
            ]);
            return;
        }

        $total = round((float) $package['price'] * (int) $quantity, 2);
        $order = (new Order($this->db))->create([
            'user_id' => $customerId,
            'package_id' => $packageId,
            'quantity' => (int) $quantity,
            'total' => $total,
            'discount' => 0,
            'final_total' => $total,
            'status' => 'Pending',
            'order_channel' => 'Telephone',
            'created_by' => (int) ($_SESSION['customer']['id'] ?? 0) ?: null,
        ]);

        unset($_SESSION['telephone_order_customer_id']);
        $_SESSION['flash'] = 'Telephone order #' . (int) $order['id'] . ' created successfully.';
        redirect('/crm/customer?id=' . $customerId);
    }

    public function cancelTelephoneOrder(): void
    {
        unset($_SESSION['telephone_order_customer_id']);
        $_SESSION['flash'] = 'Telephone order session cleared.';
        redirect('/crm/telephone-order');
    }

    private function renderTelephoneOrder(array $errors = [], array $old = []): void
    {
        $customerModel = new Customer($this->db);
        $verifiedCustomerId = (int) ($_SESSION['telephone_order_customer_id'] ?? 0);
        $verifiedCustomer = $verifiedCustomerId > 0
            ? $customerModel->find($verifiedCustomerId)
            : null;

        if ($verifiedCustomer && ($verifiedCustomer['role'] ?? '') !== 'customer') {
            $verifiedCustomer = null;
            unset($_SESSION['telephone_order_customer_id']);
        }

        $packages = array_values(array_filter(
            (new Package($this->db))->all(),
            fn (array $package): bool => (int) $package['stock'] > 0
        ));

        $this->view('crm/telephone-order', [
            'title' => 'Telephone order',
            'verifiedCustomer' => $verifiedCustomer,
            'packages' => $packages,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function customerDetail(): void
    {
        $customerId = (int) $this->input('id');
        $customerModel = new Customer($this->db);
        $customer = $customerModel->find($customerId);

        if (!$customer || ($customer['role'] ?? '') !== 'customer') {
            $customer = null;
            http_response_code(404);
        } else {
            unset(
                $customer['password'],
                $customer['credit_card'],
                $customer['card_type'],
                $customer['card_expiry']
            );
        }

        $this->view('crm/customer-detail', [
            'title' => $customer
                ? $customer['full_name'] . ' - Customer profile'
                : 'Customer not found',
            'customer' => $customer,
            'orders' => $customer
                ? (new Order($this->db))->forUser($customerId)
                : [],
            'cards' => $customer
                ? $customerModel->paymentMethods($customerId)
                : [],
            'enquiries' => $customer
                ? (new Enquiry($this->db))->forUser($customerId)
                : [],
            'subscription' => $customer
                ? (new Subscription($this->db))->currentForUser($customerId)
                : null,
            'planChanges' => $customer
                ? (new AuditLog($this->db))->subscriptionChanges($customerId)
                : [],
        ]);
    }

    public function enquiries(): void
    {
        $enquiries = (new Enquiry($this->db))->all();
        $customers = [];

        foreach ($enquiries as $enquiry) {
            $key = !empty($enquiry['user_id'])
                ? 'customer-' . (int) $enquiry['user_id']
                : 'guest-' . (int) $enquiry['id'];

            if (!isset($customers[$key])) {
                $customers[$key] = [
                    'user_id' => $enquiry['user_id'] ? (int) $enquiry['user_id'] : null,
                    'enquiry_id' => (int) $enquiry['id'],
                    'full_name' => $enquiry['full_name'] ?? 'Guest',
                    'email' => $enquiry['email'] ?? 'No account email',
                    'latest_subject' => $enquiry['subject'],
                    'latest_package' => $enquiry['package_name'] ?? 'General enquiry',
                    'latest_at' => $enquiry['created_at'],
                    'message_count' => 0,
                    'pending_count' => 0,
                ];
            }

            $customers[$key]['message_count']++;
            if ($enquiry['status'] !== 'Answered') {
                $customers[$key]['pending_count']++;
            }
        }

        $this->view('crm/enquiries', [
            'title' => 'Customer messages',
            'customers' => array_values($customers),
        ]);
    }

    public function customerEnquiries(): void
    {
        $customerId = (int) $this->input('id');
        $customer = (new Customer($this->db))->find($customerId);
        $enquiries = $customer
            ? (new Enquiry($this->db))->forUser($customerId)
            : [];

        if (!$customer) {
            http_response_code(404);
        } else {
            unset(
                $customer['password'],
                $customer['credit_card'],
                $customer['card_type'],
                $customer['card_expiry']
            );
        }

        $this->view('crm/customer-enquiries', [
            'title' => $customer
                ? $customer['full_name'] . ' - Messages'
                : 'Customer not found',
            'customer' => $customer,
            'enquiries' => $enquiries,
        ]);
    }

    public function replyEnquiry(): void
    {
        $enquiryId = (int) $this->input('enquiry_id');
        $reply = $this->input('reply');
        $customerId = (int) $this->input('customer_id');

        if ($enquiryId < 1 || $reply === '') {
            $_SESSION['flash'] = 'Please enter a reply before sending.';
            redirect($customerId > 0
                ? '/crm/enquiries/customer?id=' . $customerId
                : '/crm/enquiries');
        }

        (new Enquiry($this->db))->reply($enquiryId, $reply);
        $_SESSION['flash'] = 'Reply sent to the customer.';

        redirect($customerId > 0
            ? '/crm/enquiries/customer?id=' . $customerId
            : '/crm/enquiries');
    }
}

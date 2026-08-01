<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if ($customer = $this->currentCustomer()) {
            redirect(
                in_array($customer['role'], ['admin', 'csr'], true)
                    ? '/crm'
                    : '/account'
            );
        }

        $this->view('auth/login', [
            'title' => 'Login',
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
        ]);

        unset(
            $_SESSION['form_errors'],
            $_SESSION['old_input']
        );
    }

    public function authenticate(): void
    {
        $email = strtolower(trim($this->input('email')));
        $password = $this->input('password');
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email.';
        }

        if ($password === '') {
            $errors['password'] = 'Please enter your password.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = ['email' => $email];
            redirect('/login');
        }

        $customer = (new Customer($this->db))
            ->findByEmail($email);

        if (
            !$customer ||
            !password_verify($password, $customer['password'])
        ) {
            $_SESSION['form_errors'] = [
                'general' => 'Email or password is incorrect.'
            ];

            $_SESSION['old_input'] = ['email' => $email];
            redirect('/login');
        }

        unset($customer['password']);

        session_regenerate_id(true);
        $_SESSION['customer'] = $customer;

        $destination = $_SESSION['intended_url']
            ?? (in_array($customer['role'], ['admin', 'csr'], true)
                ? '/crm'
                : '/account');

        if (BASE_PATH !== '' && ($destination === BASE_PATH || str_starts_with($destination, BASE_PATH . '/'))) {
            $destination = substr($destination, strlen(BASE_PATH)) ?: '/';
        }
        if (!str_starts_with($destination, '/')) {
            $destination = '/account';
        }

        unset($_SESSION['intended_url']);
        redirect($destination);
    }

    public function register(): void
    {
        if ($this->currentCustomer()) {
            redirect('/account');
        }

        $this->view('auth/register', [
            'title' => 'Register',
            'errors' => $_SESSION['form_errors'] ?? [],
            'old' => $_SESSION['old_input'] ?? []
        ]);

        unset(
            $_SESSION['form_errors'],
            $_SESSION['old_input']
        );
    }

    public function store(): void
    {
        $name = trim($this->input('name'));
        $email = strtolower(trim($this->input('email')));
        $phone = trim($this->input('phone'));
        $address = trim($this->input('address'));
        $password = $this->input('password');
        $confirm = $this->input('password_confirmation');
        $terms = isset($_POST['terms']);

        $errors = [];

        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Please enter your full name.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email.';
        }

        if (!preg_match('/^[0-9+()\s-]{8,20}$/', $phone)) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }

        if (mb_strlen($address) < 5) {
            $errors['address'] = 'Please enter your address.';
        }

        if (strlen($password) < 8) {
            $errors['password'] =
                'Password must have at least 8 characters.';
        }

        if ($password !== $confirm) {
            $errors['password_confirmation'] =
                'Passwords do not match.';
        }

        if (!$terms) {
            $errors['terms'] =
                'You must accept the Terms and Privacy Policy.';
        }

        $customerModel = new Customer($this->db);

        if (
            !isset($errors['email']) &&
            $customerModel->findByEmail($email)
        ) {
            $errors['email'] =
                'This email is already registered.';
        }

        if ($errors) {
            $_SESSION['form_errors'] = $errors;

            $_SESSION['old_input'] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'terms' => $terms
            ];

            redirect('/register');
        }

        $customer = $customerModel->create([
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'password' => password_hash(
                $password,
                PASSWORD_DEFAULT
            )
        ]);

        $_SESSION['registration_confirmation'] = [
            'name' => $customer['full_name'],
            'email' => $customer['email']
        ];

        redirect('/register/success');
    }

    public function registrationSuccess(): void
    {
        $confirmation =
            $_SESSION['registration_confirmation'] ?? null;

        if (!$confirmation) {
            redirect('/register');
        }

        unset($_SESSION['registration_confirmation']);

        $this->view('auth/register-success', [
            'title' => 'Registration Complete',
            'confirmation' => $confirmation
        ]);
    }

    public function forgotPassword(): void
    {
        $this->view('auth/forgot-password', [
            'title' => 'Forgot Password',
            'verified' => isset($_SESSION['reset_user_id']),
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? ''
        ]);

        unset(
            $_SESSION['error'],
            $_SESSION['success']
        );
    }

    public function verifyResetAccount(): void
    {
        $email = strtolower(trim($this->input('email')));
        $phone = trim($this->input('phone'));

        if ($email === '' || $phone === '') {
            $_SESSION['error'] =
                'Please enter your email and phone number.';

            redirect('/forgot-password');
        }

        $customer = (new Customer($this->db))
            ->findByEmailAndPhone($email, $phone);

        if (!$customer) {
            $_SESSION['error'] =
                'Email or telephone number is incorrect.';

            redirect('/forgot-password');
        }

        $_SESSION['reset_user_id'] = $customer['id'];
        $_SESSION['success'] =
            'Account verified. You can now change your password.';

        redirect('/forgot-password');
    }

    public function resetPassword(): void
    {
        $userId = $_SESSION['reset_user_id'] ?? null;
        $password = $this->input('password');
        $confirm = $this->input('confirm_password');

        if (!$userId) {
            redirect('/forgot-password');
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] =
                'Password must have at least 8 characters.';

            redirect('/forgot-password');
        }

        if ($password !== $confirm) {
            $_SESSION['error'] =
                'Passwords do not match.';

            redirect('/forgot-password');
        }

        (new Customer($this->db))->updatePassword(
            $userId,
            password_hash($password, PASSWORD_DEFAULT)
        );

        unset($_SESSION['reset_user_id']);

        $_SESSION['password_changed'] = true;
        redirect('/login');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->rejectInvalidCsrf('/account');
        }

        unset($_SESSION['customer']);

        session_regenerate_id(true);

        $_SESSION['flash'] =
            'You have logged out successfully.';

        redirect('/login');
    }
}

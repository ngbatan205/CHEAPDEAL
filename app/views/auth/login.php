<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center g-4">

            <div class="col-lg-5">
                <div class="auth-intro h-100 p-4 p-lg-5 rounded-4">
                    <span class="badge text-bg-light mb-3">
                        WELCOME BACK
                    </span>

                    <h1 class="display-5 fw-bold">
                        Log in to CheapDeals
                    </h1>

                    <p class="lead">
                        Manage your account and explore our latest packages.
                    </p>

                    <ul class="list-unstyled auth-benefits">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            View and update your account
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Manage your package orders
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Access personalised offers
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card auth-card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">

                        <h2 class="h3 fw-bold">Account login</h2>

                        <p class="text-secondary mb-4">
                            Enter your registered email and password.
                        </p>

                        <?php if (!empty($_SESSION['password_changed'])): ?>
                            <div class="alert alert-success" role="status">
                                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                <span>Password changed successfully. You can now log in.</span>
                            </div>
                            <?php unset($_SESSION['password_changed']); ?>
                        <?php endif; ?>

                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                                <span><?= e($errors['general']) ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="<?= url('/login') ?>" data-login-form>
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label" for="login-email">
                                    Email address
                                </label>

                                <input
                                    id="login-email"
                                    class="form-control form-control-lg
                                    <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                    type="email"
                                    name="email"
                                    value="<?= e($old['email'] ?? '') ?>"
                                    placeholder="name@example.com"
                                    required
                                >

                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= e($errors['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="login-password">
                                    Password
                                </label>

                                <div class="password-field">
                                    <input
                                        id="login-password"
                                        class="form-control form-control-lg
                                        <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                        type="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <button
                                        class="password-field-toggle"
                                        type="button"
                                        data-password-toggle="login-password"
                                        data-password-label="password"
                                        aria-label="Show password"
                                    >
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>

                                </div>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback d-block">
                                        <?= e($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="text-end mb-4">
                                <a href="<?= url('/forgot-password') ?>">
                                    Forgot password?
                                </a>
                            </div>

                            <button class="btn btn-primary btn-lg w-100">
                                Log in
                            </button>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            Do not have an account?
                            <a class="fw-bold" href="<?= url('/register') ?>">
                                Create an account
                            </a>
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

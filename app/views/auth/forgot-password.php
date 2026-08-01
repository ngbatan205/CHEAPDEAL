<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <h1 class="h3 fw-bold">Reset password</h1>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?= e($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?= e($success) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($_SESSION['reset_user_id'])): ?>

                            <p class="text-secondary">
                                Verify your registered email and telephone number.
                            </p>

                            <form method="post" action="<?= url('/forgot-password/verify') ?>">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label" for="reset-email">Email address</label>
                                    <input
                                        id="reset-email"
                                        class="form-control"
                                        type="email"
                                        name="email"
                                        autocomplete="email"
                                        required
                                    >
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="reset-phone">Telephone number</label>
                                    <input
                                        id="reset-phone"
                                        class="form-control"
                                        type="tel"
                                        name="phone"
                                        autocomplete="tel"
                                        required
                                    >
                                </div>

                                <button class="btn btn-primary w-100">
                                    Verify account
                                </button>
                            </form>

                        <?php else: ?>

                            <p class="text-success">
                                Account verified. Enter your new password.
                            </p>

                            <form method="post" action="<?= url('/forgot-password/reset') ?>">
                                <?= csrf_field() ?>

                                <div class="mb-3">
                                    <label class="form-label" for="new-password">New password</label>

                                    <div class="password-field">
                                        <input
                                            class="form-control"
                                            id="new-password"
                                            type="password"
                                            name="password"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            class="password-field-toggle"
                                            type="button"
                                            data-password-toggle="new-password"
                                            data-password-label="new password"
                                            aria-label="Show new password"
                                        >
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="confirm-password">
                                        Confirm new password
                                    </label>

                                    <div class="password-field">
                                        <input
                                            class="form-control"
                                            id="confirm-password"
                                            type="password"
                                            name="confirm_password"
                                            minlength="8"
                                            autocomplete="new-password"
                                            required
                                        >

                                        <button
                                            class="password-field-toggle"
                                            type="button"
                                            data-password-toggle="confirm-password"
                                            data-password-label="new password confirmation"
                                            aria-label="Show new password confirmation"
                                        >
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <button class="btn btn-primary w-100">
                                    Change password
                                </button>
                            </form>

                        <?php endif; ?>

                        <div class="text-center mt-3">
                            <a href="<?= url('/login') ?>">Back to login</a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

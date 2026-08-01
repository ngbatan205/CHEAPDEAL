<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center align-items-stretch g-4">

            <!-- Left introduction -->
            <div class="col-lg-5">
                <div class="auth-intro h-100 p-4 p-lg-5 rounded-4">

                    <span class="badge text-bg-light mb-3">
                        CREATE ACCOUNT
                    </span>

                    <h1 class="display-5 fw-bold mb-3">
                        Join CheapDeals
                    </h1>

                    <p class="lead mb-4">
                        Create a customer profile to manage your
                        mobile, broadband and tablet packages.
                    </p>

                    <ul class="list-unstyled auth-benefits mb-4">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Browse affordable packages
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Manage your account online
                        </li>

                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            Receive personalised offers
                        </li>
                    </ul>

                    <div class="alert alert-light border-0 mb-0">
                        <strong>Quick registration:</strong>
                        payment details will only be requested when
                        you place an order or settle a bill.
                    </div>

                </div>
            </div>

            <!-- Register form -->
            <div class="col-lg-7">
                <div class="card auth-card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">

                        <h2 class="h3 fw-bold mb-1">
                            Create your profile
                        </h2>

                        <p class="text-secondary mb-4">
                            Complete the information below to create
                            your CheapDeals customer account.
                        </p>

                        <form
                            method="post"
                            action="<?= url('/register') ?>"
                            novalidate
                            data-register-form
                        >
                            <?= csrf_field() ?>
                            <div class="row g-3">

                                <!-- Full name -->
                                <div class="col-md-6">
                                    <label
                                        for="register-name"
                                        class="form-label"
                                    >
                                        Full name *
                                    </label>

                                    <input
                                        id="register-name"
                                        class="form-control
                                        <?= isset($errors['name'])
                                            ? 'is-invalid'
                                            : '' ?>"
                                        type="text"
                                        name="name"
                                        value="<?= e(
                                            $old['name'] ?? ''
                                        ) ?>"
                                        autocomplete="name"
                                        placeholder="Your full name"
                                        required
                                    >

                                    <?php if (
                                        isset($errors['name'])
                                    ): ?>
                                        <div class="invalid-feedback">
                                            <?= e($errors['name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label
                                        for="register-email"
                                        class="form-label"
                                    >
                                        Email address *
                                    </label>

                                    <input
                                        id="register-email"
                                        class="form-control
                                        <?= isset($errors['email'])
                                            ? 'is-invalid'
                                            : '' ?>"
                                        type="email"
                                        name="email"
                                        value="<?= e(
                                            $old['email'] ?? ''
                                        ) ?>"
                                        autocomplete="email"
                                        placeholder="name@example.com"
                                        required
                                    >

                                    <?php if (
                                        isset($errors['email'])
                                    ): ?>
                                        <div class="invalid-feedback">
                                            <?= e($errors['email']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Phone -->
                                <div class="col-md-6">
                                    <label
                                        for="register-phone"
                                        class="form-label"
                                    >
                                        Telephone number *
                                    </label>

                                    <input
                                        id="register-phone"
                                        class="form-control
                                        <?= isset($errors['phone'])
                                            ? 'is-invalid'
                                            : '' ?>"
                                        type="tel"
                                        name="phone"
                                        value="<?= e(
                                            $old['phone'] ?? ''
                                        ) ?>"
                                        autocomplete="tel"
                                        placeholder="+44 7700 900000"
                                        required
                                    >

                                    <?php if (
                                        isset($errors['phone'])
                                    ): ?>
                                        <div class="invalid-feedback">
                                            <?= e($errors['phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Address -->
                                <div class="col-md-6">
                                    <label
                                        for="register-address"
                                        class="form-label"
                                    >
                                        Address *
                                    </label>

                                    <textarea
                                        id="register-address"
                                        class="form-control
                                        <?= isset($errors['address'])
                                            ? 'is-invalid'
                                            : '' ?>"
                                        name="address"
                                        rows="3"
                                        autocomplete="street-address"
                                        placeholder="Your home address"
                                        required
                                    ><?= e(
                                        $old['address'] ?? ''
                                    ) ?></textarea>

                                    <?php if (
                                        isset($errors['address'])
                                    ): ?>
                                        <div class="invalid-feedback">
                                            <?= e($errors['address']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label
                                        for="register-password"
                                        class="form-label"
                                    >
                                        Password *
                                    </label>

                                    <div class="password-field">
                                        <input
                                            id="register-password"
                                            class="form-control
                                            <?= isset($errors['password'])
                                                ? 'is-invalid'
                                                : '' ?>"
                                            type="password"
                                            name="password"
                                            minlength="8"
                                            autocomplete="new-password"
                                            placeholder="At least 8 characters"
                                            required
                                        >

                                        <button
                                            class="password-field-toggle"
                                            type="button"
                                            data-password-toggle=
                                            "register-password"
                                            data-password-label="new password"
                                            aria-label="Show new password"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>

                                    </div>
                                    <?php if (
                                        isset($errors['password'])
                                    ): ?>
                                        <div class="invalid-feedback d-block">
                                            <?= e($errors['password']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Confirm password -->
                                <div class="col-md-6">
                                    <label
                                        for=
                                        "register-password-confirmation"
                                        class="form-label"
                                    >
                                        Confirm password *
                                    </label>

                                    <div class="password-field">
                                        <input
                                            id=
                                            "register-password-confirmation"
                                            class="form-control
                                            <?= isset(
                                                $errors[
                                                    'password_confirmation'
                                                ]
                                            )
                                                ? 'is-invalid'
                                                : '' ?>"
                                            type="password"
                                            name="password_confirmation"
                                            minlength="8"
                                            autocomplete="new-password"
                                            placeholder="Repeat password"
                                            required
                                        >

                                        <button
                                            class="password-field-toggle"
                                            type="button"
                                            data-password-toggle=
                                            "register-password-confirmation"
                                            data-password-label="password confirmation"
                                            aria-label="Show password confirmation"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>

                                    </div>
                                    <?php if (isset($errors['password_confirmation'])): ?>
                                        <div class="invalid-feedback d-block">
                                            <?= e($errors['password_confirmation']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <!-- Terms -->
                            <div class="form-check mt-4">
                                <input
                                    id="register-terms"
                                    class="form-check-input
                                    <?= isset($errors['terms'])
                                        ? 'is-invalid'
                                        : '' ?>"
                                    type="checkbox"
                                    name="terms"
                                    value="1"
                                    <?= !empty($old['terms'])
                                        ? 'checked'
                                        : '' ?>
                                    required
                                >

                                <label
                                    class="form-check-label"
                                    for="register-terms"
                                >
                                    I agree to the Terms and
                                    Privacy Policy.
                                </label>

                                <?php if (
                                    isset($errors['terms'])
                                ): ?>
                                    <div class="invalid-feedback">
                                        <?= e($errors['terms']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button
                                class="btn btn-primary
                                btn-lg w-100 mt-4"
                                type="submit"
                            >
                                Create account
                            </button>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            Already have an account?

                            <a
                                class="fw-bold"
                                href="<?= url('/login') ?>"
                            >
                                Log in
                            </a>
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

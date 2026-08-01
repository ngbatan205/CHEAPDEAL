<section class="telephone-order-page">
    <header class="telephone-order-hero">
        <div>
            <p class="eyebrow">Assisted sales</p>
            <h1>Telephone ordering</h1>
            <p>Verify the caller, retrieve or create their account, then place an order on their behalf.</p>
        </div>
        <a class="btn small" href="<?= url('/crm') ?>">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to dashboard
        </a>
    </header>

    <?php if ($errors): ?>
        <div class="telephone-errors" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!$verifiedCustomer): ?>
        <div class="telephone-step-heading">
            <span>1</span>
            <div>
                <h2>Identify and verify the caller</h2>
                <p>Use the registered details for an existing customer, or validate the required details before creating a new profile.</p>
            </div>
        </div>

        <div class="telephone-caller-grid">
            <section class="telephone-panel <?= ($old['mode'] ?? '') === 'existing' ? 'is-active' : '' ?>">
                <div class="telephone-panel-title">
                    <i class="bi bi-person-check"></i>
                    <div>
                        <p class="eyebrow">Existing customer</p>
                        <h2>Verify an account</h2>
                    </div>
                </div>
                <p class="telephone-panel-copy">Enter the caller's registered telephone number to find and retrieve their account.</p>
                <form method="post" action="<?= url('/crm/telephone-order/verify') ?>">
                    <?= csrf_field() ?>
                    <label for="existing-phone">Registered telephone</label>
                    <input
                        id="existing-phone"
                        name="phone"
                        type="tel"
                        value="<?= e($old['existing_phone'] ?? '') ?>"
                        autocomplete="tel"
                        placeholder="+84 900 000 000"
                        required
                    >

                    <button class="btn primary" type="submit">
                        <i class="bi bi-search"></i> Find account
                    </button>
                </form>
            </section>

            <section class="telephone-panel <?= ($old['mode'] ?? '') === 'new' ? 'is-active' : '' ?>">
                <div class="telephone-panel-title">
                    <i class="bi bi-person-plus"></i>
                    <div>
                        <p class="eyebrow">New customer</p>
                        <h2>Create a verified profile</h2>
                    </div>
                </div>
                <p class="telephone-panel-copy">Confirm these details with the caller before creating their customer profile.</p>
                <form method="post" action="<?= url('/crm/telephone-order/create-customer') ?>">
                    <?= csrf_field() ?>
                    <div class="telephone-field-grid">
                        <div>
                            <label for="new-name">Full name</label>
                            <input id="new-name" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" autocomplete="name" required>
                        </div>
                        <div>
                            <label for="new-phone">Telephone</label>
                            <input id="new-phone" name="phone" type="tel" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel" required>
                        </div>
                    </div>

                    <label for="new-email">Email</label>
                    <input id="new-email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required>

                    <label for="new-address">Address</label>
                    <textarea id="new-address" name="address" rows="3" autocomplete="street-address" required><?= e($old['address'] ?? '') ?></textarea>

                    <label for="new-password">Temporary password</label>
                    <input id="new-password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                    <small>At least 8 characters. Ask the customer to change it after signing in.</small>

                    <button class="btn primary" type="submit">
                        <i class="bi bi-person-check"></i> Create and verify
                    </button>
                </form>
            </section>
        </div>
    <?php else: ?>
        <div class="telephone-step-heading">
            <span><i class="bi bi-check-lg"></i></span>
            <div>
                <h2>Caller verified</h2>
                <p>The account has been retrieved and is ready for telephone ordering.</p>
            </div>
        </div>

        <section class="telephone-verified-card">
            <div class="telephone-customer-avatar">
                <?= e(strtoupper(substr($verifiedCustomer['full_name'], 0, 1))) ?>
            </div>
            <div>
                <strong><?= e($verifiedCustomer['full_name']) ?></strong>
                <span><i class="bi bi-envelope"></i> <?= e($verifiedCustomer['email']) ?></span>
                <span><i class="bi bi-telephone"></i> <?= e($verifiedCustomer['phone'] ?? '') ?></span>
                <span><i class="bi bi-geo-alt"></i> <?= e($verifiedCustomer['address'] ?? 'No address') ?></span>
            </div>
            <form method="post" action="<?= url('/crm/telephone-order/cancel') ?>">
                <?= csrf_field() ?>
                <button class="btn small" type="submit">Change caller</button>
            </form>
        </section>

        <div class="telephone-step-heading">
            <span>2</span>
            <div>
                <h2>Create the telephone order</h2>
                <p>Select the package requested by the customer. The order will be created as pending.</p>
            </div>
        </div>

        <section class="telephone-panel telephone-order-panel">
            <?php if ($packages): ?>
                <form method="post" action="<?= url('/crm/telephone-order/place') ?>" id="telephone-order-form" data-telephone-order-form>
                    <?= csrf_field() ?>
                    <div class="telephone-order-fields">
                        <div>
                            <label for="telephone-package">Package</label>
                            <select id="telephone-package" name="package_id" required>
                                <option value="">Select a package</option>
                                <?php foreach ($packages as $package): ?>
                                    <option
                                        value="<?= (int) $package['id'] ?>"
                                        data-price="<?= e(number_format((float) $package['price'], 2, '.', '')) ?>"
                                        data-stock="<?= (int) $package['stock'] ?>"
                                        <?= (string) ($old['package_id'] ?? '') === (string) $package['id'] ? 'selected' : '' ?>
                                    >
                                        <?= e($package['package_name']) ?> — <?= e($package['category']) ?>
                                        (£<?= number_format((float) $package['price'], 2) ?>, <?= (int) $package['stock'] ?> available)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="telephone-quantity">Quantity</label>
                            <input
                                id="telephone-quantity"
                                name="quantity"
                                type="number"
                                min="1"
                                max="99"
                                value="<?= e($old['quantity'] ?? '1') ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="telephone-order-total">
                        <span>
                            <small>Order channel</small>
                            <strong><i class="bi bi-telephone-fill"></i> Telephone</strong>
                        </span>
                        <span>
                            <small>Available stock</small>
                            <strong id="telephone-stock">—</strong>
                        </span>
                        <span>
                            <small>Order total</small>
                            <strong id="telephone-total">£0.00</strong>
                        </span>
                    </div>

                    <div class="telephone-order-actions">
                        <button class="btn primary" type="submit">
                            <i class="bi bi-telephone-outbound"></i> Place telephone order
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p>No packages are currently in stock.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</section>

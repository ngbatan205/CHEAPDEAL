<?php
$requestedTab = (string) ($_GET['tab'] ?? 'personal');
$activeTab = in_array($requestedTab, ['personal', 'payment', 'password'], true)
    ? $requestedTab
    : 'personal';
$showAddCard = isset($_GET['add_card']);
?>

<section class="section profile-page">
    <header class="section-heading profile-heading">
        <div>
            <p class="eyebrow">Your account</p>
            <h1>Profile and security</h1>
            <p>Keep your contact details, payment methods and password up to date.</p>
        </div>
        <span class="profile-email"><i class="bi bi-envelope" aria-hidden="true"></i> <?= e($customer['email']) ?></span>
    </header>

    <div class="profile-layout">
        <nav class="profile-tabs" aria-label="Profile sections" role="tablist">
            <button
                class="profile-tab<?= $activeTab === 'personal' ? ' active' : '' ?>"
                id="personal-tab"
                data-bs-toggle="tab"
                data-bs-target="#personal"
                type="button"
                role="tab"
                aria-controls="personal"
                aria-selected="<?= $activeTab === 'personal' ? 'true' : 'false' ?>"
            >
                <i class="bi bi-person" aria-hidden="true"></i>
                <span><strong>Personal details</strong><small>Name, phone and address</small></span>
            </button>
            <button
                class="profile-tab<?= $activeTab === 'payment' ? ' active' : '' ?>"
                id="payment-tab"
                data-bs-toggle="tab"
                data-bs-target="#payment"
                type="button"
                role="tab"
                aria-controls="payment"
                aria-selected="<?= $activeTab === 'payment' ? 'true' : 'false' ?>"
            >
                <i class="bi bi-credit-card" aria-hidden="true"></i>
                <span><strong>Payment methods</strong><small>Verified saved cards</small></span>
            </button>
            <button
                class="profile-tab<?= $activeTab === 'password' ? ' active' : '' ?>"
                id="password-tab"
                data-bs-toggle="tab"
                data-bs-target="#password"
                type="button"
                role="tab"
                aria-controls="password"
                aria-selected="<?= $activeTab === 'password' ? 'true' : 'false' ?>"
            >
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <span><strong>Password</strong><small>Account sign-in security</small></span>
            </button>
        </nav>

        <div class="tab-content profile-content">
            <section
                class="tab-pane fade<?= $activeTab === 'personal' ? ' show active' : '' ?> profile-panel"
                id="personal"
                role="tabpanel"
                aria-labelledby="personal-tab"
                tabindex="0"
            >
                <div class="profile-panel-heading">
                    <div><p class="eyebrow">Contact details</p><h2>Personal information</h2></div>
                    <i class="bi bi-person-vcard" aria-hidden="true"></i>
                </div>
                <p class="profile-panel-intro">We use these details for orders, receipts and support enquiries.</p>
                <form class="profile-form" method="post" action="<?= url('/profile') ?>">
                    <?= csrf_field() ?>
                    <div class="form-grid two-columns">
                        <div>
                            <label for="profile-name">Full name</label>
                            <input id="profile-name" name="name" value="<?= e($customer['full_name']) ?>" autocomplete="name" required>
                        </div>
                        <div>
                            <label for="profile-email">Email address</label>
                            <input id="profile-email" type="email" value="<?= e($customer['email']) ?>" autocomplete="email" disabled>
                            <small>Email is used as your sign-in name.</small>
                        </div>
                        <div>
                            <label for="profile-phone">Phone number</label>
                            <input id="profile-phone" name="phone" type="tel" value="<?= e($customer['phone']) ?>" autocomplete="tel" required>
                        </div>
                        <div class="form-field-wide">
                            <label for="profile-address">Address</label>
                            <textarea id="profile-address" name="address" rows="4" autocomplete="street-address" required><?= e($customer['address']) ?></textarea>
                        </div>
                    </div>
                    <div class="profile-form-actions"><button class="btn primary" type="submit">Save personal details</button></div>
                </form>
            </section>

            <section
                class="tab-pane fade<?= $activeTab === 'payment' ? ' show active' : '' ?> profile-panel"
                id="payment"
                role="tabpanel"
                aria-labelledby="payment-tab"
                tabindex="0"
            >
                <div class="profile-panel-heading">
                    <div><p class="eyebrow">Billing</p><h2>Payment methods</h2></div>
                    <button
                        class="btn primary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#add-card-form"
                        aria-expanded="<?= $showAddCard ? 'true' : 'false' ?>"
                        aria-controls="add-card-form"
                    ><i class="bi bi-plus-lg" aria-hidden="true"></i> Add card</button>
                </div>
                <p class="profile-panel-intro">Only the card type, last four digits, expiry and verification reference are stored.</p>

                <?php if (!empty($cards)): ?>
                    <div class="saved-card-grid">
                        <?php foreach ($cards as $card): ?>
                            <article class="saved-card">
                                <header>
                                    <span class="saved-card-brand"><?= e($card['card_type']) ?></span>
                                    <div class="saved-card-badges">
                                        <?php if (!empty($card['is_default'])): ?><span class="status-pill is-success">Default</span><?php endif; ?>
                                        <?php if (($card['verification_status'] ?? '') === 'Approved'): ?><span class="status-pill is-success"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Verified</span><?php endif; ?>
                                    </div>
                                </header>
                                <p class="saved-card-number" aria-label="Card ending <?= e($card['card_last4']) ?>">•••• •••• •••• <?= e($card['card_last4']) ?></p>
                                <p class="saved-card-expiry"><span>Expires</span><strong><?= e($card['card_expiry']) ?></strong></p>
                                <div class="saved-card-actions">
                                    <?php if (empty($card['is_default'])): ?>
                                        <form method="post" action="<?= url('/profile/payment/default') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="card_id" value="<?= (int) $card['id'] ?>">
                                            <button class="btn small" type="submit">Set as default</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="post" action="<?= url('/profile/payment/delete') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="card_id" value="<?= (int) $card['id'] ?>">
                                        <button class="btn small text-danger" type="submit" data-confirm="Remove this saved payment method?">Remove</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state compact-empty-state">
                        <i class="bi bi-credit-card" aria-hidden="true"></i>
                        <h3>No saved cards</h3>
                        <p>Add and verify a card when you are ready.</p>
                    </div>
                <?php endif; ?>

                <div class="collapse<?= $showAddCard ? ' show' : '' ?> add-card-panel" id="add-card-form">
                    <form method="post" action="<?= url('/profile/payment/add') ?>" data-add-card-form data-visacheck-url="<?= url('/profile/payment/verify') ?>">
                        <?= csrf_field() ?>
                        <div class="profile-panel-heading add-card-heading">
                            <div><p class="eyebrow">Card check</p><h3>Add a payment method</h3></div>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                        </div>
                        <div class="visacheck-profile-intro">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <div>
                                <strong>VISAcheck card verification</strong>
                                <span>Use one of the safe sample cards below. Never enter a real card number in this demonstration.</span>
                            </div>
                        </div>
                        <div class="visacheck-test-cards" aria-label="Fictional sample cards">
                            <button type="button" data-visacheck-test-card data-card-type="Visa" data-card-number="4111111111111111"><strong>Visa</strong><span>4111 … 1111</span></button>
                            <button type="button" data-visacheck-test-card data-card-type="Mastercard" data-card-number="5555555555554444"><strong>Mastercard</strong><span>5555 … 4444</span></button>
                            <button type="button" data-visacheck-test-card data-card-type="JCB" data-card-number="3530111333300000"><strong>JCB</strong><span>3530 … 0000</span></button>
                        </div>
                        <div class="form-grid two-columns">
                            <div>
                                <label for="card-type">Card type</label>
                                <select id="card-type" name="card_type" autocomplete="cc-type" required>
                                    <option value="">Select card type</option>
                                    <option value="Visa">Visa</option>
                                    <option value="Mastercard">Mastercard</option>
                                    <option value="JCB">JCB</option>
                                </select>
                            </div>
                            <div>
                                <label for="card-number">Card number</label>
                                <input id="card-number" name="card_number" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="1234 5678 9012 3456" data-card-number-format required>
                                <small>Enter 13–19 digits.</small>
                            </div>
                            <div>
                                <label for="card-expiry">Expiry date</label>
                                <input id="card-expiry" name="card_expiry" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])/[0-9]{2}" required>
                            </div>
                            <div>
                                <label for="card-cvv">Security code</label>
                                <input id="card-cvv" name="cvv" type="password" inputmode="numeric" autocomplete="cc-csc" maxlength="3" pattern="[0-9]{3}" placeholder="123" required>
                                <small>The security code is never stored.</small>
                            </div>
                        </div>
                        <div class="visacheck-profile-status" data-visacheck-status aria-live="polite">
                            <i class="bi bi-info-circle" aria-hidden="true"></i><span>Card has not been verified.</span>
                        </div>
                        <div class="profile-form-actions">
                            <button class="btn primary" type="button" data-visacheck-verify><i class="bi bi-shield-check" aria-hidden="true"></i> Verify card</button>
                            <button class="btn" type="submit" data-visacheck-save disabled>Save card</button>
                            <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#add-card-form" aria-controls="add-card-form" data-card-form-cancel>Cancel</button>
                        </div>
                    </form>
                </div>
            </section>

            <section
                class="tab-pane fade<?= $activeTab === 'password' ? ' show active' : '' ?> profile-panel"
                id="password"
                role="tabpanel"
                aria-labelledby="password-tab"
                tabindex="0"
            >
                <div class="profile-panel-heading">
                    <div><p class="eyebrow">Security</p><h2>Change password</h2></div>
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                </div>
                <p class="profile-panel-intro">Choose a new password with at least eight characters.</p>
                <form class="profile-form password-form" method="post" action="<?= url('/profile/password') ?>">
                    <?= csrf_field() ?>
                    <div>
                        <label for="current-password">Current password</label>
                        <input id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                    </div>
                    <div>
                        <label for="new-password">New password</label>
                        <input id="new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div>
                        <label for="confirm-password">Confirm new password</label>
                        <input id="confirm-password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="profile-form-actions"><button class="btn primary" type="submit">Change password</button></div>
                </form>
            </section>
        </div>
    </div>
</section>

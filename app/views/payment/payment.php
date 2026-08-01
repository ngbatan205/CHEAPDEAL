<?php
$selectedOffer = $selectedOffer ?? '';
$selectedPayment = $old['payment_method'] ?? '';
$cardIsExpired = static function (string $expiry): bool {
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $parts)) {
        return true;
    }
    return (int) ('20' . $parts[2]) * 100 + (int) $parts[1] < (int) date('Ym');
};

if (str_starts_with($selectedPayment, 'card_')) {
    $selectedCardId = (int) substr($selectedPayment, 5);
    $selectedCardIsValid = false;
    foreach ($cards as $card) {
        if ((int) $card['id'] === $selectedCardId && !$cardIsExpired((string) $card['card_expiry'])) {
            $selectedCardIsValid = true;
            break;
        }
    }
    if (!$selectedCardIsValid) {
        $selectedPayment = '';
    }
}
if ($selectedPayment === '') {
    foreach ($cards as $card) {
        if (!empty($card['is_default']) && !$cardIsExpired((string) $card['card_expiry'])) {
            $selectedPayment = 'card_' . $card['id'];
            break;
        }
    }
}
if ($selectedPayment === '') {
    $selectedPayment = 'new_card';
}
?>

<section class="luxury-checkout">
    <header class="checkout-heading">
        <div>
            <a href="<?= $source === 'cart' ? url('/checkout') : ($source === 'deal' ? url('/combos') : url('/packages')) ?>" class="checkout-back">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <p class="eyebrow">Secure checkout</p>
            <h1>Complete your payment</h1>
            <p>Review your order and choose how you would like to pay.</p>
        </div>
        <div class="secure-payment-badge">
            <i class="bi bi-shield-lock-fill"></i>
            <span><strong>Secure payment</strong><small>Protected checkout</small></span>
        </div>
    </header>

    <?php if ($errors): ?>
        <div class="payment-alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <div>
                <strong>We could not process your payment</strong>
                <p>Please review the highlighted information below.</p>
            </div>
        </div>
    <?php endif; ?>

    <form
        class="checkout-grid"
        method="post"
        action="<?= url('/payment') ?>"
        data-payment-checkout
        data-subtotal="<?= e(number_format((float) $total, 2, '.', '')) ?>"
        data-app-discount-percent="<?= (int) $appDiscountPercent ?>"
    >
        <?= csrf_field() ?>
        <input type="hidden" name="source" value="<?= e($source) ?>">
        <?php if ($source === 'direct'): ?>
            <input type="hidden" name="package_id" value="<?= (int) $items[0]['id'] ?>">
        <?php elseif ($source === 'deal'): ?>
            <input type="hidden" name="deal_id" value="<?= (int) $dealId ?>">
        <?php endif; ?>

        <div class="checkout-main">
            <section class="checkout-panel order-review-panel">
                <div class="panel-title">
                    <span class="panel-number">1</span>
                    <div>
                        <h2>Order summary</h2>
                        <p><?= count($items) ?> package<?= count($items) === 1 ? '' : 's' ?> selected</p>
                    </div>
                </div>

                <div class="checkout-products">
                    <?php foreach ($items as $item): ?>
                        <article class="checkout-product">
                            <img src="<?= e($item['image']) ?>" alt="<?= e($item['package_name']) ?>">
                            <div>
                                <span><?= e($item['category']) ?></span>
                                <h3><?= e($item['package_name']) ?></h3>
                                <p>Quantity <?= (int) $item['quantity'] ?> · Monthly plan</p>
                            </div>
                            <strong>&pound;<?= number_format((float) $item['line_total'], 2) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($categoryCount === 1 && $eligibleOffers): ?>
                    <div class="package-offer-box<?= isset($errors['offer_code']) ? ' has-error' : '' ?>" data-package-offer>
                        <button class="package-offer-toggle" type="button" data-offer-toggle>
                            <span><i class="bi bi-ticket-perforated"></i> Enter offer code</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="package-offer-entry<?= $selectedOffer !== '' || isset($errors['offer_code']) ? ' is-open' : '' ?>" data-offer-entry>
                            <div class="offer-input-row">
                                <input
                                    name="offer_code"
                                    value="<?= e($selectedOffer) ?>"
                                    list="eligible-offer-codes"
                                    placeholder="Enter your offer code"
                                    autocomplete="off"
                                    class="<?= isset($errors['offer_code']) ? 'is-invalid' : '' ?>"
                                    data-offer-input
                                >
                                <button class="btn primary" type="button" data-apply-offer>Apply</button>
                                <button class="btn no-offer-button" type="button" data-remove-offer>No offer</button>
                            </div>
                            <datalist id="eligible-offer-codes">
                                <?php foreach ($eligibleOffers as $offer): ?>
                                    <option value="<?= e($offer['code']) ?>"><?= (int) $offer['discount_percent'] ?>% off</option>
                                <?php endforeach; ?>
                            </datalist>
                            <div class="eligible-offer-list">
                                <span>Available for this package:</span>
                                <?php foreach ($eligibleOffers as $offer): ?>
                                    <button
                                        type="button"
                                        data-offer-code="<?= e($offer['code']) ?>"
                                        data-offer-percent="<?= (int) $offer['discount_percent'] ?>"
                                    >
                                        <strong><?= e($offer['code']) ?></strong>
                                        <small><?= (int) $offer['discount_percent'] ?>% off</small>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($errors['offer_code'])): ?>
                                <small class="field-error"><?= e($errors['offer_code']) ?></small>
                            <?php endif; ?>
                            <p class="offer-apply-message<?= $offerApplied ? ' is-success' : '' ?>" data-offer-message>
                                <?= $offerApplied
                                    ? e($selectedOffer) . ' applied successfully.'
                                    : 'Choose the available code above, then press Apply.' ?>
                            </p>
                        </div>
                    </div>
                <?php elseif ($categoryCount === 1): ?>
                    <div class="checkout-bundle-notice">
                        <i class="bi bi-percent" aria-hidden="true"></i>
                        <span>
                            <strong>15% app-order promotion applied automatically</strong>
                            <small>No additional offer codes are active at the moment.</small>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="checkout-bundle-notice">
                        <i class="bi bi-gift-fill"></i>
                        <span>
                            <strong>15% app-order promotion applied automatically</strong>
                            <small>Every order placed through the app receives the same fixed 15% baseline discount.</small>
                        </span>
                    </div>
                <?php endif; ?>
            </section>

            <?php if (false): ?>
            <section class="checkout-panel">
                <div class="panel-title">
                    <span class="panel-number">2</span>
                    <div>
                        <h2>Automatic package saving</h2>
                        <p>Your discount is based on the number of different categories.</p>
                    </div>
                </div>

                <div class="offer-selector" hidden aria-hidden="true">
                    <label for="checkout-offer">Available offers</label>
                    <select id="checkout-offer" name="offer_code" data-offer-select class="<?= isset($errors['offer_code']) ? 'is-invalid' : '' ?>">
                        <option value="" data-percent="0">No offer — pay the standard price</option>
                        <?php foreach ($offers as $offer): ?>
                            <option
                                value="<?= e($offer['code']) ?>"
                                data-percent="<?= (int) $offer['discount_percent'] ?>"
                                <?= $selectedOffer === $offer['code'] ? 'selected' : '' ?>
                            >
                                <?= e($offer['code']) ?> — <?= (int) $offer['discount_percent'] ?>% off
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['offer_code'])): ?>
                        <small class="field-error"><?= e($errors['offer_code']) ?></small>
                    <?php endif; ?>
                    <div class="offer-detail" data-offer-detail>
                        <i class="bi bi-stars"></i>
                        <span>Choose an offer to reduce your monthly rental.</span>
                    </div>
                </div>

                <div class="bundle-saving-card<?= $bundleName ? ' is-qualified' : '' ?>">
                    <div class="bundle-saving-icon">
                        <i class="bi <?= $bundleName ? 'bi-gift-fill' : 'bi-layers' ?>"></i>
                    </div>
                    <div class="bundle-saving-copy">
                        <?php if ($bundleName): ?>
                            <span>Bundle saving applied</span>
                            <h3><?= e($bundleName) ?></h3>
                            <p>
                                <?= (int) $categoryCount ?> different categories unlock
                                <?= (int) $discountPercent ?>% off your monthly rental.
                            </p>
                        <?php else: ?>
                            <span>Build your bundle</span>
                            <h3>Single package</h3>
                            <p>Add a package from another category to unlock 15% off.</p>
                        <?php endif; ?>
                    </div>
                    <div class="bundle-saving-value">
                        <strong><?= (int) $discountPercent ?>%</strong>
                        <small>OFF</small>
                    </div>
                </div>

                <div class="bundle-rules">
                    <span class="<?= $categoryCount >= 2 ? 'is-active' : '' ?>">
                        <i class="bi bi-check-circle-fill"></i>
                        Double package · 2 categories · 15% off
                    </span>
                    <span class="<?= $categoryCount >= 3 ? 'is-active' : '' ?>">
                        <i class="bi bi-check-circle-fill"></i>
                        Triple package · 3 categories · 15% off
                    </span>
                </div>
            </section>
            <?php endif; ?>

            <section class="checkout-panel">
                <div class="panel-title">
                    <span class="panel-number">2</span>
                    <div>
                        <h2>Payment method</h2>
                        <p>Select a saved card or add a new one.</p>
                    </div>
                </div>

                <?php if (isset($errors['payment_method'])): ?>
                    <p class="field-error"><?= e($errors['payment_method']) ?></p>
                <?php endif; ?>

                <?php if ($cards): ?>
                    <div class="saved-card-options">
                        <?php foreach ($cards as $card): ?>
                            <?php
                            $cardValue = 'card_' . $card['id'];
                            $isExpired = $cardIsExpired((string) $card['card_expiry']);
                            ?>
                            <label class="saved-card-choice<?= $isExpired ? ' is-expired' : '' ?>">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="<?= e($cardValue) ?>"
                                    <?= $isExpired ? 'disabled' : '' ?>
                                    <?= $selectedPayment === $cardValue ? 'checked' : '' ?>
                                >
                                <span class="card-brand-mark"><?= e(strtoupper(substr($card['card_type'], 0, 1))) ?></span>
                                <span class="saved-card-copy">
                                    <strong><?= e($card['card_type']) ?> •••• <?= e($card['card_last4']) ?></strong>
                                    <small><?= $isExpired ? 'Expired' : 'Expires ' . e($card['card_expiry']) ?></small>
                                </span>
                                <?php if (!empty($card['is_default'])): ?>
                                    <span class="default-card-label">Default</span>
                                <?php endif; ?>
                                <i class="bi bi-check-circle-fill card-check"></i>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label
                    class="new-card-choice"
                    data-new-card-toggle
                >
                    <input
                        type="radio"
                        name="payment_method"
                        value="new_card"
                        <?= $selectedPayment === 'new_card' ? 'checked' : '' ?>
                    >
                    <span><i class="bi bi-plus-lg"></i></span>
                    <strong>Add a new payment card</strong>
                    <i class="bi bi-chevron-down"></i>
                </label>

                <div class="new-card-fields<?= $selectedPayment === 'new_card' ? ' is-open' : '' ?>" data-new-card-fields>
                    <div class="accepted-cards">
                        <span>Accepted cards</span>
                        <strong>VISA</strong><strong>Mastercard</strong><strong>JCB</strong>
                    </div>

                    <div class="visa-check-sandbox">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>VISAcheck card verification</strong>
                            <span>Select a safe sample card below. The card type, number, expiry date and security code must all pass validation.</span>
                            <small>Never enter a real card number in this demonstration.</small>
                        </div>
                    </div>

                    <div class="visacheck-test-cards payment-test-cards" aria-label="Safe sample cards">
                        <button type="button" data-payment-test-card data-card-type="Visa" data-card-number="4111111111111111"><strong>Visa sample</strong><span>4111 … 1111</span></button>
                        <button type="button" data-payment-test-card data-card-type="Mastercard" data-card-number="5555555555554444"><strong>Mastercard sample</strong><span>5555 … 4444</span></button>
                        <button type="button" data-payment-test-card data-card-type="JCB" data-card-number="3530111333300000"><strong>JCB sample</strong><span>3530 … 0000</span></button>
                    </div>

                    <div class="card-form-grid">
                        <label class="full-field">
                            Card type
                            <select name="card_type" class="<?= isset($errors['card_type']) ? 'is-invalid' : '' ?>">
                                <option value="">Select card type</option>
                                <?php foreach (['Visa', 'Mastercard', 'JCB'] as $type): ?>
                                    <option value="<?= $type ?>" <?= ($old['card_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['card_type'])): ?><small class="field-error"><?= e($errors['card_type']) ?></small><?php endif; ?>
                        </label>

                        <label class="full-field">
                            Card number
                            <span class="card-number-input">
                                <i class="bi bi-credit-card-2-front"></i>
                                <input name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="Select a sample card above" class="<?= isset($errors['card_number']) ? 'is-invalid' : '' ?>">
                            </span>
                            <?php if (isset($errors['card_number'])): ?><small class="field-error"><?= e($errors['card_number']) ?></small><?php endif; ?>
                        </label>

                        <label>
                            Expiry date
                            <input name="card_expiry" autocomplete="cc-exp" placeholder="MM/YY" value="<?= e($old['card_expiry'] ?? '') ?>" class="<?= isset($errors['card_expiry']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($errors['card_expiry'])): ?><small class="field-error"><?= e($errors['card_expiry']) ?></small><?php endif; ?>
                        </label>

                        <label>
                            Security code
                            <span class="cvv-input">
                                <input name="cvv" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="CVV" class="<?= isset($errors['cvv']) ? 'is-invalid' : '' ?>">
                                <i class="bi bi-question-circle" title="3 or 4 digits on your card"></i>
                            </span>
                            <?php if (isset($errors['cvv'])): ?><small class="field-error"><?= e($errors['cvv']) ?></small><?php endif; ?>
                        </label>
                    </div>

                    <label class="save-card-toggle">
                        <input type="checkbox" name="save_card" value="1" <?= !isset($old['save_card']) || $old['save_card'] ? 'checked' : '' ?>>
                        <span><i class="bi bi-check"></i></span>
                        Save this card securely for future payments
                    </label>
                </div>
            </section>
        </div>

        <div class="checkout-sidebar">
            <div class="payment-totals-card">
                <div class="totals-heading">
                    <span>Payment total</span>
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="total-row">
                    <span>Monthly subtotal</span>
                    <strong>&pound;<?= number_format((float) $total, 2) ?></strong>
                </div>
                <div class="total-row discount-row">
                    <span data-discount-label>
                        15% app discount<?= $offerApplied ? ' + ' . e($selectedOffer) . ' offer' : '' ?>
                    </span>
                    <strong data-discount-amount>&minus;&pound;<?= number_format((float) $discountAmount, 2) ?></strong>
                </div>
                <p class="text-secondary small">App discount: &minus;&pound;<?= number_format((float) $appDiscountAmount, 2) ?><?php if ($offerApplied): ?> · offer applied second: &minus;&pound;<?= number_format((float) $offerDiscountAmount, 2) ?><?php endif; ?></p>
                <div class="grand-total">
                    <div>
                        <span>Total due today</span>
                        <small>Including applicable discounts</small>
                    </div>
                    <strong data-final-total>&pound;<?= number_format((float) $finalTotal, 2) ?></strong>
                </div>

                <button class="premium-pay-button" type="submit">
                    <i class="bi bi-lock-fill"></i>
                    <span>Pay securely <strong data-pay-button-total>&pound;<?= number_format((float) $finalTotal, 2) ?></strong></span>
                </button>

                <div class="payment-assurance">
                    <div><i class="bi bi-shield-check"></i><span>VISAcheck<br><small>Verified</small></span></div>
                    <div><i class="bi bi-arrow-counterclockwise"></i><span>Protected<br><small>Safe payment</small></span></div>
                    <div><i class="bi bi-headset"></i><span>Support<br><small>Here to help</small></span></div>
                </div>
            </div>
        </div>
    </form>
</section>

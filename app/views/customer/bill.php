<?php if (!$payments): ?>
    <section class="section narrow">
        <h1>Bill not found</h1>
        <p>This bill does not exist or does not belong to your account.</p>
        <a class="btn primary" href="<?= url('/account') ?>">Back to My order</a>
    </section>
<?php else: ?>
    <?php
    $firstPayment = $payments[0];
    $subtotal = array_sum(array_column($payments, 'total'));
    $discount = array_sum(array_column($payments, 'discount'));
    $paidTotal = array_sum(array_column($payments, 'amount'));
    ?>

    <section class="receipt-page">
        <div class="receipt-toolbar">
            <a class="checkout-back" href="<?= url('/account') ?>">
                <i class="bi bi-arrow-left"></i> Back to My order
            </a>
            <button class="btn" type="button" data-print-page>
                <i class="bi bi-printer"></i> Print bill
            </button>
        </div>

        <article class="receipt-document">
            <header class="receipt-header">
                <div class="receipt-brand">
                    <span><i class="bi bi-check-lg"></i></span>
                    <div>
                        <strong>CheapDeals</strong>
                        <small>Payment receipt</small>
                    </div>
                </div>
                <div class="receipt-paid">
                    <i class="bi bi-patch-check-fill"></i>
                    PAID
                </div>
            </header>

            <div class="receipt-title">
                <div>
                    <p class="eyebrow">Thank you for your payment</p>
                    <h1>Your bill</h1>
                    <p>A copy of this receipt is available anytime from My order.</p>
                </div>
                <div class="receipt-amount">
                    <small>Amount paid</small>
                    <strong>&pound;<?= number_format((float) $paidTotal, 2) ?></strong>
                </div>
            </div>

            <div class="receipt-meta">
                <div>
                    <small>Transaction reference</small>
                    <strong><?= e($reference) ?></strong>
                </div>
                <div>
                    <small>Payment date</small>
                    <strong><?= e(date('d M Y, H:i', strtotime($firstPayment['payment_date']))) ?></strong>
                </div>
                <div>
                    <small>Payment method</small>
                    <strong><i class="bi bi-credit-card"></i> <?= e($firstPayment['payment_method']) ?></strong>
                </div>
                <div>
                    <small>VISAcheck</small>
                    <?php if (($firstPayment['verification_status'] ?? '') === 'Approved'): ?>
                        <strong class="visa-check-approved">
                            <i class="bi bi-patch-check-fill"></i> Approved
                        </strong>
                        <span><?= e($firstPayment['verification_reference'] ?? '') ?></span>
                    <?php else: ?>
                        <strong>Legacy payment</strong>
                    <?php endif; ?>
                </div>
            </div>

            <div class="receipt-customer">
                <div>
                    <small>Billed to</small>
                    <strong><?= e($firstPayment['full_name']) ?></strong>
                    <span><?= e($firstPayment['email']) ?></span>
                    <span><?= e($firstPayment['phone'] ?? '') ?></span>
                </div>
                <div>
                    <small>Billing address</small>
                    <strong><?= nl2br(e($firstPayment['address'] ?? 'Not provided')) ?></strong>
                </div>
            </div>

            <div class="receipt-items">
                <div class="receipt-item receipt-item-head">
                    <span>Description</span>
                    <span>Category</span>
                    <span>Amount</span>
                </div>
                <?php foreach ($payments as $payment): ?>
                    <div class="receipt-item">
                        <span>
                            <strong><?= e($payment['package_name'] ?? 'Package') ?></strong>
                            <small>Order #<?= (int) $payment['order_id'] ?></small>
                        </span>
                        <span><?= e($payment['category'] ?? 'Service') ?></span>
                        <span>&pound;<?= number_format((float) $payment['total'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="receipt-summary">
                <div><span>Subtotal</span><strong>&pound;<?= number_format((float) $subtotal, 2) ?></strong></div>
                <div class="receipt-discount"><span>Discount</span><strong>&minus;&pound;<?= number_format((float) $discount, 2) ?></strong></div>
                <div class="receipt-total"><span>Total paid</span><strong>&pound;<?= number_format((float) $paidTotal, 2) ?></strong></div>
            </div>

            <footer class="receipt-footer">
                <i class="bi bi-shield-check"></i>
                <p>
                    <strong>Payment completed securely</strong>
                    <?= ($firstPayment['verification_status'] ?? '') === 'Approved'
                        ? 'Card details were approved by VISAcheck.'
                        : 'This receipt confirms that your payment was recorded successfully.' ?>
                </p>
                <span>cheapdeals.com</span>
            </footer>
            <div class="alert alert-success receipt-email-notice">
                <i class="bi bi-envelope-check" aria-hidden="true"></i>
                <span><strong>Receipt copy prepared:</strong> <?= e($firstPayment['email']) ?> · reference <?= e($reference) ?>. External email delivery is not enabled in this demonstration.</span>
            </div>
        </article>
    </section>
<?php endif; ?>

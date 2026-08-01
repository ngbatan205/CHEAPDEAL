<section class="section narrow success">
    <h1>Order confirmed</h1>
    <?php if ($order): ?>
        <p>Your order for <strong><?= e($order['package_name']) ?></strong> has been created.</p>
        <dl class="summary">
            <div><dt>Order ID</dt><dd>#<?= (int) $order['id'] ?></dd></div>
            <div><dt>Total</dt><dd>$<?= number_format((float) $order['final_total'], 2) ?></dd></div>
            <div><dt>Status</dt><dd><?= e($order['status']) ?></dd></div>
        </dl>
        <a class="btn primary" href="<?= url('/payment') ?>">Continue to payment</a>
    <?php else: ?>
        <p>No recent order was found.</p>
        <a class="btn primary" href="<?= url('/packages') ?>">Browse packages</a>
    <?php endif; ?>
</section>

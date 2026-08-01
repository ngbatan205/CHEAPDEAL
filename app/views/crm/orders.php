<section class="admin-hero">
    <div>
        <p class="eyebrow">Order management</p>
        <h1>Customer orders</h1>
        <p>Review online and telephone orders, payment totals, verification outcomes and fulfilment status.</p>
    </div>
    <a class="btn primary" href="<?= url('/crm/telephone-order') ?>"><i class="bi bi-telephone-outbound" aria-hidden="true"></i> New telephone order</a>
</section>

<section class="admin-section" aria-labelledby="orders-title">
    <div class="admin-subsection-heading">
        <h2 id="orders-title">Order history</h2>
        <span class="status-pill is-muted"><?= count($orders) ?> orders</span>
    </div>
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">Customer order records</caption>
            <thead>
                <tr>
                    <th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Package</th><th scope="col">Qty</th>
                    <th scope="col">Channel</th><th scope="col">Handled by</th><th scope="col">Subtotal</th><th scope="col">Discount</th>
                    <th scope="col">Final total</th><th scope="col">Card check</th><th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php $orderStatus = strtolower((string) $order['status']); ?>
                    <tr>
                        <td><strong>#<?= (int) $order['id'] ?></strong></td>
                        <td><strong><?= e($order['full_name']) ?></strong><br><small><?= e($order['email']) ?></small></td>
                        <td><?= e($order['package_name']) ?></td>
                        <td><?= (int) ($order['quantity'] ?? 1) ?></td>
                        <td><?= e($order['order_channel'] ?? 'Website') ?></td>
                        <td><?= e($order['created_by_name'] ?? 'Customer') ?></td>
                        <td>&pound;<?= number_format((float) $order['total'], 2) ?></td>
                        <td>&minus;&pound;<?= number_format((float) $order['discount'], 2) ?></td>
                        <td><strong>&pound;<?= number_format((float) $order['final_total'], 2) ?></strong></td>
                        <td>
                            <?= ($order['verification_status'] ?? '') === 'Approved'
                                ? '<span class="visa-check-table-status"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Approved</span>'
                                : '<span class="status-pill is-muted">Not recorded</span>' ?>
                        </td>
                        <td><span class="status-pill <?= in_array($orderStatus, ['paid', 'success', 'completed'], true) ? 'is-success' : (in_array($orderStatus, ['pending', 'processing'], true) ? 'is-warning' : 'is-muted') ?>"><?= e($order['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?><tr><td colspan="11">No orders have been recorded.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

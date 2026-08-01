<section class="section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Customer area</p>
            <h1>Hello, <?= e($customer['full_name']) ?></h1>
        </div>
    </div>
    <h2>Your orders</h2>
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">Your CheapDeals order history</caption>
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Package</th>
                    <th scope="col">Category</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Channel</th>
                    <th scope="col">Original</th>
                    <th scope="col">Discount</th>
                    <th scope="col">Paid</th>
                    <th scope="col">Payment</th>
                    <th scope="col">Card check</th>
                    <th scope="col">Status</th>
                    <th scope="col">Bill</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= (int) $order['id'] ?></td>
                        <td><?= e($order['package_name']) ?></td>
                        <td><?= e($order['category']) ?></td>
                        <td><?= (int) ($order['quantity'] ?? 1) ?></td>
                        <td><?= e($order['order_channel'] ?? 'Website') ?></td>
                        <td>&pound;<?= number_format((float) $order['total'], 2) ?></td>
                        <td>&pound;<?= number_format((float) $order['discount'], 2) ?></td>
                        <td>&pound;<?= number_format((float) $order['final_total'], 2) ?></td>
                        <td>
                            <?= $order['payment_method'] ? e($order['payment_method']) : '&mdash;' ?>
                        </td>
                        <td>
                            <?= ($order['verification_status'] ?? '') === 'Approved'
                                ? '<span class="visa-check-table-status"><i class="bi bi-patch-check-fill"></i> Approved</span>'
                                : '&mdash;' ?>
                        </td>
                        <td><span class="status-pill<?= $order['status'] === 'Paid' ? ' is-success' : ' is-warning' ?>"><?= e($order['status']) ?></span></td>
                        <td>
                            <?php if (!empty($order['receipt_ref'])): ?>
                                <a class="btn small bill-link" href="<?= url('/bill?ref=' . urlencode($order['receipt_ref'])) ?>">
                                    <i class="bi bi-receipt"></i> View bill
                                </a>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?><tr><td colspan="12">No orders yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

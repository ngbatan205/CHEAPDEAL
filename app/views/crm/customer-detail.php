<?php if (!$customer): ?>
    <section class="section narrow">
        <h1>Customer not found</h1>
        <p>The requested customer account does not exist.</p>
        <a class="btn primary" href="<?= url('/crm/customers') ?>">Back to customers</a>
    </section>
<?php else: ?>
    <?php
    $paidOrders = array_filter(
        $orders,
        fn (array $order): bool => $order['status'] === 'Paid'
    );
    $totalSpent = array_sum(array_column($paidOrders, 'final_total'));
    $latestOrder = $orders[0] ?? null;
    ?>

    <section class="crm-customer-profile">
        <a class="checkout-back" href="<?= url('/crm/customers') ?>">
            <i class="bi bi-arrow-left"></i> Back to customers
        </a>

        <header class="crm-profile-hero">
            <div class="crm-profile-avatar">
                <?= e(strtoupper(substr($customer['full_name'], 0, 1))) ?>
            </div>
            <div class="crm-profile-name">
                <p class="eyebrow">Customer account</p>
                <h1><?= e($customer['full_name']) ?></h1>
                <div>
                    <span><i class="bi bi-envelope"></i> <?= e($customer['email']) ?></span>
                    <span><i class="bi bi-telephone"></i> <?= e($customer['phone'] ?? 'Not provided') ?></span>
                    <span class="crm-role-badge"><?= e(ucfirst($customer['role'] ?? 'customer')) ?></span>
                </div>
            </div>
            <div class="crm-member-since">
                <small>Customer since</small>
                <strong><?= e(date('d M Y', strtotime($customer['created_at']))) ?></strong>
            </div>
        </header>

        <div class="crm-customer-stats">
            <article>
                <i class="bi bi-bag-check"></i>
                <div><strong><?= count($orders) ?></strong><span>Total orders</span></div>
            </article>
            <article>
                <i class="bi bi-check-circle"></i>
                <div><strong><?= count($paidOrders) ?></strong><span>Paid orders</span></div>
            </article>
            <article>
                <i class="bi bi-cash-stack"></i>
                <div><strong>&pound;<?= number_format((float) $totalSpent, 2) ?></strong><span>Total paid</span></div>
            </article>
            <article>
                <i class="bi bi-chat-left-text"></i>
                <div><strong><?= count($enquiries) ?></strong><span>Enquiries</span></div>
            </article>
        </div>

        <div class="crm-profile-grid crm-subscription-grid">
            <section class="crm-profile-panel">
                <div class="crm-panel-title">
                    <div><p class="eyebrow">Current service</p><h2>Active plan</h2></div>
                    <i class="bi bi-phone"></i>
                </div>
                <?php if ($subscription): ?>
                    <div class="current-plan-summary">
                        <div><small><?= e($subscription['plan_type'] ?? 'Plan') ?></small><strong><?= e($subscription['plan_name']) ?></strong></div>
                        <span class="status-pill is-success"><?= e($subscription['status']) ?></span>
                    </div>
                    <dl class="crm-info-list compact-info-list">
                        <div><dt>Monthly price</dt><dd>&pound;<?= number_format((float) $subscription['price'], 2) ?></dd></div>
                        <div><dt>Renewal</dt><dd><?= e($subscription['renewal_date'] ?? 'Not scheduled') ?></dd></div>
                    </dl>
                <?php else: ?>
                    <p class="crm-empty-copy">This customer has no active subscription.</p>
                <?php endif; ?>
            </section>

            <section class="crm-profile-panel">
                <div class="crm-panel-title">
                    <div><p class="eyebrow">Account activity</p><h2>Recent plan changes</h2></div>
                    <a href="<?= url('/crm/subscription-changes?q=' . urlencode($customer['email'])) ?>">View history</a>
                </div>
                <?php if ($planChanges): ?>
                    <div class="customer-plan-change-list">
                        <?php foreach (array_slice($planChanges, 0, 3) as $change): ?>
                            <article>
                                <span><strong><?= e($change['previous_name']) ?></strong><i class="bi bi-arrow-right" aria-hidden="true"></i><strong><?= e($change['new_name']) ?></strong></span>
                                <small><?= e(date('d M Y, H:i', strtotime($change['created_at']))) ?> · Completed automatically</small>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="crm-empty-copy">No plan changes have been recorded for this customer.</p>
                <?php endif; ?>
            </section>
        </div>

        <div class="crm-profile-grid">
            <section class="crm-profile-panel">
                <div class="crm-panel-title">
                    <div>
                        <p class="eyebrow">Account</p>
                        <h2>Personal information</h2>
                    </div>
                    <i class="bi bi-person-vcard"></i>
                </div>
                <dl class="crm-info-list">
                    <div><dt>Customer ID</dt><dd>#<?= (int) $customer['id'] ?></dd></div>
                    <div><dt>Full name</dt><dd><?= e($customer['full_name']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($customer['email']) ?></dd></div>
                    <div><dt>Phone</dt><dd><?= e($customer['phone'] ?? 'Not provided') ?></dd></div>
                    <div><dt>Address</dt><dd><?= nl2br(e($customer['address'] ?? 'Not provided')) ?></dd></div>
                    <div><dt>Role</dt><dd><?= e(ucfirst($customer['role'] ?? 'customer')) ?></dd></div>
                </dl>
            </section>

            <section class="crm-profile-panel">
                <div class="crm-panel-title">
                    <div>
                        <p class="eyebrow">Billing</p>
                        <h2>Saved payment methods</h2>
                    </div>
                    <i class="bi bi-credit-card"></i>
                </div>
                <?php if ($cards): ?>
                    <div class="crm-card-list">
                        <?php foreach ($cards as $card): ?>
                            <div>
                                <span class="crm-card-icon"><i class="bi bi-credit-card-2-front"></i></span>
                                <span>
                                    <strong><?= e($card['card_type']) ?> &bull;&bull;&bull;&bull; <?= e($card['card_last4']) ?></strong>
                                    <small>Expires <?= e($card['card_expiry']) ?></small>
                                </span>
                                <?php if (!empty($card['is_default'])): ?>
                                    <span class="default-card-label">Default</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="crm-empty-copy">No saved payment methods.</p>
                <?php endif; ?>
            </section>
        </div>

        <section class="crm-profile-panel crm-orders-panel">
            <div class="crm-panel-title">
                <div>
                    <p class="eyebrow">Order history</p>
                    <h2>Packages ordered</h2>
                </div>
                <?php if ($latestOrder): ?>
                    <span class="crm-latest-order">Latest order #<?= (int) $latestOrder['id'] ?></span>
                <?php endif; ?>
            </div>

            <div class="table-wrap">
                <table>
                    <caption class="visually-hidden">Order history for <?= e($customer['full_name']) ?></caption>
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Package</th>
                            <th scope="col">Category</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Channel</th>
                            <th scope="col">Handled by</th>
                            <th scope="col">Date</th>
                            <th scope="col">Original</th>
                            <th scope="col">Discount</th>
                            <th scope="col">Paid</th>
                            <th scope="col">Payment method</th>
                            <th scope="col">Status</th>
                            <th scope="col">Bill reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><strong><?= e($order['package_name'] ?? 'Unknown package') ?></strong></td>
                                <td><?= e($order['category'] ?? '') ?></td>
                                <td><?= (int) ($order['quantity'] ?? 1) ?></td>
                                <td><?= e($order['order_channel'] ?? 'Website') ?></td>
                                <td><?= e($order['created_by_name'] ?? 'Customer') ?></td>
                                <td><?= e(date('d M Y', strtotime($order['created_at']))) ?></td>
                                <td>&pound;<?= number_format((float) $order['total'], 2) ?></td>
                                <td>&pound;<?= number_format((float) $order['discount'], 2) ?></td>
                                <td>&pound;<?= number_format((float) $order['final_total'], 2) ?></td>
                                <td><?= e($order['payment_method'] ?? 'Not paid') ?></td>
                                <td><span class="crm-order-status status-<?= e(strtolower($order['status'])) ?>"><?= e($order['status']) ?></span></td>
                                <td><?= e($order['receipt_ref'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$orders): ?>
                            <tr><td colspan="13">This customer has not placed any orders.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="crm-profile-panel">
            <div class="crm-panel-title">
                <div>
                    <p class="eyebrow">Support history</p>
                    <h2>Recent enquiries</h2>
                </div>
                <a href="<?= url('/crm/enquiries') ?>">Open support inbox</a>
            </div>
            <?php if ($enquiries): ?>
                <div class="crm-enquiry-summary">
                    <?php foreach (array_slice($enquiries, 0, 5) as $enquiry): ?>
                        <article>
                            <span class="message-status <?= $enquiry['status'] === 'Answered' ? 'is-answered' : '' ?>">
                                <?= e($enquiry['status']) ?>
                            </span>
                            <div>
                                <strong><?= e($enquiry['subject']) ?></strong>
                                <small><?= e($enquiry['package_name'] ?? 'General enquiry') ?> &bull; <?= e(date('d M Y', strtotime($enquiry['created_at']))) ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="crm-empty-copy">This customer has not sent any enquiries.</p>
            <?php endif; ?>
        </section>
    </section>
<?php endif; ?>

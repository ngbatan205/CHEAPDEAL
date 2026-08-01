<section class="admin-hero">
    <div>
        <p class="eyebrow">Customer support</p>
        <h1>Enquiry inbox</h1>
        <p>Open a customer conversation, review its context and provide a clear response from one workspace.</p>
    </div>
    <span class="workspace-role"><i class="bi bi-chat-square-text" aria-hidden="true"></i> <?= count($customers) ?> customer thread<?= count($customers) === 1 ? '' : 's' ?></span>
</section>

<section class="admin-section admin-enquiries" aria-label="Customer enquiry inbox">
    <div class="admin-customer-inbox">
        <?php foreach ($customers as $customer): ?>
            <?php if ($customer['user_id']): ?>
                <a class="admin-inbox-customer" href="<?= url('/crm/enquiries/customer?id=' . (int) $customer['user_id']) ?>">
            <?php else: ?>
                <div class="admin-inbox-customer is-disabled">
            <?php endif; ?>
                    <span class="admin-inbox-avatar" aria-hidden="true"><?= e(strtoupper(substr($customer['full_name'], 0, 1))) ?></span>
                    <span class="admin-inbox-identity"><strong><?= e($customer['full_name']) ?></strong><small><?= e($customer['email']) ?></small></span>
                    <span class="admin-inbox-preview">
                        <strong><?= e($customer['latest_subject']) ?></strong>
                        <small><?= e($customer['latest_package']) ?> &bull; <?= e(date('d M Y, H:i', strtotime($customer['latest_at']))) ?></small>
                    </span>
                    <span class="admin-inbox-counts">
                        <?php if ($customer['pending_count'] > 0): ?>
                            <span class="message-status"><?= (int) $customer['pending_count'] ?> pending</span>
                        <?php else: ?>
                            <span class="message-status is-answered">All answered</span>
                        <?php endif; ?>
                        <small><?= (int) $customer['message_count'] ?> conversation<?= $customer['message_count'] === 1 ? '' : 's' ?></small>
                    </span>
                    <?php if ($customer['user_id']): ?><i class="bi bi-chevron-right" aria-hidden="true"></i><?php endif; ?>
            <?php if ($customer['user_id']): ?></a><?php else: ?></div><?php endif; ?>
        <?php endforeach; ?>

        <?php if (!$customers): ?>
            <div class="empty-state"><i class="bi bi-chat-square-text" aria-hidden="true"></i><h2>No enquiries yet</h2><p>New customer messages will appear here.</p></div>
        <?php endif; ?>
    </div>
</section>

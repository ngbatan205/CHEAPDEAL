<?php
$dashboardRole = (string) ($_SESSION['customer']['role'] ?? 'csr');
$isDashboardAdmin = $dashboardRole === 'admin';
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Operations workspace</p>
        <h1><?= $isDashboardAdmin ? 'Administration dashboard' : 'Customer service dashboard' ?></h1>
        <p>
            <?= $isDashboardAdmin
                ? 'Monitor customer activity, maintain the catalogue and review protected operational records.'
                : 'Find customers, place telephone orders, check catalogue availability and respond to enquiries.' ?>
        </p>
    </div>
    <span class="workspace-role">
        <i class="bi <?= $isDashboardAdmin ? 'bi-shield-check' : 'bi-headset' ?>" aria-hidden="true"></i>
        <?= $isDashboardAdmin ? 'Administrator access' : 'CSR access' ?>
    </span>
</section>

<section class="admin-section" aria-labelledby="overview-title">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Current records</p>
            <h2 id="overview-title">Store overview</h2>
            <p>Select a summary card to open its full workspace.</p>
        </div>
    </div>

    <div class="stats-grid">
        <a href="<?= url('/crm/packages') ?>"><strong><?= count($packages) ?></strong><span>Catalogue packages</span></a>
        <a href="<?= url('/crm/orders') ?>"><strong><?= count($orders) ?></strong><span>Customer orders</span></a>
        <a href="<?= url('/crm/customers') ?>"><strong><?= count($customers) ?></strong><span>Customer accounts</span></a>
        <a href="<?= url('/crm/enquiries') ?>"><strong><?= count($enquiries) ?></strong><span>Support enquiries</span></a>
    </div>

    <div class="crm-quick-actions" aria-label="Quick actions">
        <a class="btn primary" href="<?= url('/crm/telephone-order') ?>">
            <i class="bi bi-telephone-outbound" aria-hidden="true"></i> Start telephone order
        </a>
        <a class="btn" href="<?= url('/crm/customers') ?>">
            <i class="bi bi-person-plus" aria-hidden="true"></i> Find or create customer
        </a>
        <a class="btn" href="<?= url('/crm/enquiries') ?>">
            <i class="bi bi-chat-square-text" aria-hidden="true"></i> Review enquiries
        </a>
        <a class="btn" href="<?= url('/crm/subscription-changes') ?>">
            <i class="bi bi-arrow-left-right" aria-hidden="true"></i> Review plan changes
        </a>
        <?php if ($isDashboardAdmin): ?>
            <a class="btn" href="<?= url('/crm/offers') ?>">
                <i class="bi bi-ticket-perforated" aria-hidden="true"></i> Manage offers
            </a>
            <a class="btn" href="<?= url('/crm/records') ?>">
                <i class="bi bi-database-lock" aria-hidden="true"></i> Review operational records
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="admin-section" aria-labelledby="recent-plan-changes-title">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Customer activity</p>
            <h2 id="recent-plan-changes-title">Recent plan changes</h2>
            <p>Changes are completed automatically after validation. Use this list for confirmation and customer support; CSR approval is not required.</p>
        </div>
        <a class="btn" href="<?= url('/crm/subscription-changes') ?>">View all activity</a>
    </div>

    <?php if ($planChanges): ?>
        <div class="recent-operation-list">
            <?php foreach (array_slice($planChanges, 0, 5) as $change): ?>
                <article>
                    <span class="recent-operation-icon" aria-hidden="true"><i class="bi bi-arrow-left-right"></i></span>
                    <div>
                        <strong><?= e($change['customer_name']) ?></strong>
                        <span><?= e($change['previous_name']) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i> <?= e($change['new_name']) ?></span>
                        <small><?= e(date('d M Y, H:i', strtotime($change['created_at']))) ?> · <?= e($change['channel']) ?></small>
                    </div>
                    <span class="status-pill is-success">Completed</span>
                    <a class="btn small" href="<?= url('/crm/customer?id=' . (int) $change['customer_id']) ?>">Open</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state compact-empty-state">
            <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
            <h3>No plan changes yet</h3>
            <p>Completed customer changes will appear here automatically.</p>
        </div>
    <?php endif; ?>
</section>

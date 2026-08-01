<section class="admin-hero">
    <div>
        <p class="eyebrow">Customer account activity</p>
        <h1>Plan-change activity</h1>
        <p>Review completed self-service plan changes and open the customer record when follow-up is needed.</p>
    </div>
    <a class="btn" href="<?= url('/crm') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to dashboard</a>
</section>

<section class="admin-section" aria-labelledby="plan-change-workflow-title">
    <div class="workflow-callout">
        <span class="workflow-callout-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
        <div>
            <h2 id="plan-change-workflow-title">How this workflow works</h2>
            <p>Eligible customer changes are validated and completed automatically. CSR staff use this history to confirm the previous and new plan, answer questions and follow up only when the customer asks for help. No manual approval is required.</p>
        </div>
        <span class="status-pill is-success">Automatic completion</span>
    </div>

    <form class="activity-filter" method="get" action="<?= url('/crm/subscription-changes') ?>" role="search">
        <label for="plan-change-search">Search plan-change activity</label>
        <div>
            <input id="plan-change-search" name="q" value="<?= e($query ?? '') ?>" placeholder="Customer, email, plan or status">
            <button class="btn primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            <?php if (($query ?? '') !== ''): ?>
                <a class="btn" href="<?= url('/crm/subscription-changes') ?>">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($changes): ?>
        <div class="table-wrap plan-change-table">
            <table>
                <caption><?= count($changes) ?> completed plan change<?= count($changes) === 1 ? '' : 's' ?><?= ($query ?? '') !== '' ? ' matching your search' : '' ?></caption>
                <thead>
                    <tr>
                        <th scope="col">Completed</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Previous plan</th>
                        <th scope="col">New plan</th>
                        <th scope="col">Channel</th>
                        <th scope="col">Status</th>
                        <th scope="col">Customer record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($changes as $change): ?>
                        <tr>
                            <td><time datetime="<?= e($change['created_at']) ?>"><?= e(date('d M Y, H:i', strtotime($change['created_at']))) ?></time></td>
                            <td>
                                <strong><?= e($change['customer_name']) ?></strong>
                                <?php if ($change['customer_email'] !== ''): ?><small><?= e($change['customer_email']) ?></small><?php endif; ?>
                            </td>
                            <td><span class="plan-name-cell"><small><?= e($change['previous_type']) ?></small><?= e($change['previous_name']) ?></span></td>
                            <td><span class="plan-name-cell"><small><?= e($change['new_type']) ?></small><strong><?= e($change['new_name']) ?></strong></span></td>
                            <td><?= e($change['channel']) ?></td>
                            <td><span class="status-pill is-success"><i class="bi bi-check2" aria-hidden="true"></i> <?= e($change['status']) ?></span></td>
                            <td><a class="btn small" href="<?= url('/crm/customer?id=' . (int) $change['customer_id']) ?>">Open customer</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state activity-empty-state">
            <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
            <h2>No plan changes found</h2>
            <p><?= ($query ?? '') !== '' ? 'Try a different customer, email or plan name.' : 'Completed customer plan changes will appear here automatically.' ?></p>
        </div>
    <?php endif; ?>
</section>

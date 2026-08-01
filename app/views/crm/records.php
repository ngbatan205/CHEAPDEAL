<section class="admin-hero">
    <div>
        <p class="eyebrow">Protected records</p>
        <h1>Operational records</h1>
        <p>Review customer, order, payment, enquiry and catalogue activity without altering protected business history.</p>
    </div>
    <span class="workspace-role"><i class="bi bi-lock" aria-hidden="true"></i> Read-only access</span>
</section>

<section class="admin-section" aria-labelledby="records-title">
    <div class="alert alert-info">
        <i class="bi bi-shield-lock" aria-hidden="true"></i>
        <span><strong>Privacy control:</strong> full card numbers and security codes are never retained or displayed. Records can only change through their owning workflow.</span>
    </div>

    <form class="admin-filter-bar" method="get" action="<?= url('/crm/records') ?>">
        <label for="record-type">Record type
            <select id="record-type" name="type">
                <option value="">All types</option>
                <?php foreach (['customer', 'order', 'payment', 'enquiry', 'catalogue'] as $type): ?>
                    <option value="<?= $type ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label for="record-status">Status
            <input id="record-status" name="status" value="<?= e($selectedStatus) ?>" placeholder="Paid, success or pending">
        </label>
        <label for="record-search">Search records
            <input id="record-search" name="q" value="<?= e($query) ?>" placeholder="Reference, date or value">
        </label>
        <button class="btn primary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Apply filters</button>
        <a class="btn" href="<?= url('/crm/records') ?>">Reset</a>
    </form>

    <div class="admin-subsection-heading">
        <h2 id="records-title">Matching records</h2>
        <span class="status-pill is-muted"><?= count($records) ?> results</span>
    </div>
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">Filtered operational records</caption>
            <thead><tr><th scope="col">Type</th><th scope="col">Reference</th><th scope="col">Status</th><th scope="col">Summary</th><th scope="col">Date or value</th><th scope="col">Action</th></tr></thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <?php $recordStatus = strtolower((string) $record['status']); ?>
                    <tr>
                        <td><?= e(ucfirst($record['type'])) ?></td>
                        <td><strong><?= e($record['reference']) ?></strong></td>
                        <td><span class="status-pill <?= in_array($recordStatus, ['paid', 'success', 'approved', 'answered', 'active'], true) ? 'is-success' : (in_array($recordStatus, ['pending', 'processing'], true) ? 'is-warning' : 'is-muted') ?>"><?= e($record['status']) ?></span></td>
                        <td><?= e($record['summary']) ?></td>
                        <td><?= e($record['created_at']) ?></td>
                        <td><a class="btn small" href="<?= url('/crm/record?type=' . urlencode($record['type']) . '&id=' . (int) $record['id']) ?>">View details</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($records)): ?><tr><td colspan="6">No operational records match the selected filters.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

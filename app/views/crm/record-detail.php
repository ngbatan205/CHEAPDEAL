<section class="admin-hero">
    <div>
        <p class="eyebrow">Protected record</p>
        <h1><?= e($record['reference'] ?? 'Record not found') ?></h1>
        <p>Review the stored values for this record. Direct overwrite and hard deletion are unavailable.</p>
    </div>
    <a class="btn" href="<?= url('/crm/records') ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to records</a>
</section>

<section class="admin-section">
    <?php if ($record): ?>
        <div class="alert alert-info"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>This record can only change through its owning business workflow.</span></div>
        <dl class="record-detail-list">
            <?php foreach (($record['details'] ?? []) as $label => $value): ?>
                <div><dt><?= e($label) ?></dt><dd><?= e((string) $value) ?></dd></div>
            <?php endforeach; ?>
        </dl>
    <?php else: ?>
        <div class="empty-state"><i class="bi bi-search" aria-hidden="true"></i><h2>Record not found</h2><p>The selected record may no longer be available.</p></div>
    <?php endif; ?>
</section>

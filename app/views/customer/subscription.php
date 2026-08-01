<section class="section">
    <div class="section-heading">
        <div><p class="eyebrow">Account and subscription</p><h1>My subscription</h1></div>
        <a href="<?= url('/account') ?>">Back to account</a>
    </div>

    <?php if ($subscription): ?>
        <div class="form-card">
            <p class="eyebrow">Current subscription · <?= e($subscription['plan_type'] ?? 'Package') ?></p>
            <h2><?= e($subscription['plan_name']) ?></h2>
            <p><?= e($subscription['description']) ?></p>
            <div class="stats-grid">
                <div><strong>&pound;<?= number_format((float) $subscription['price'], 2) ?></strong><span>Monthly price</span></div>
                <div><strong><?= (int) $subscription['minutes'] ?></strong><span>Minutes</span></div>
                <div><strong><?= (int) $subscription['sms'] ?></strong><span>SMS</span></div>
                <div><strong><?= (int) $subscription['data_gb'] ?>GB</strong><span>Data</span></div>
            </div>
            <p>Status: <strong><?= e($subscription['status']) ?></strong> · Renewal: <strong><?= e($subscription['renewal_date'] ?? 'Not scheduled') ?></strong></p>
        </div>
    <?php else: ?>
        <div class="form-card"><h2>No active subscription</h2><p>Choose an available package below to start your subscription.</p></div>
    <?php endif; ?>

    <div class="form-card spaced-card">
        <p class="eyebrow">Amend or upgrade</p>
        <h2>Compare and choose a new package or combo</h2>
        <p>Your current subscription stays active until you confirm a different available option below. Eligible changes are validated and completed immediately.</p>
        <form method="post" action="<?= url('/subscription/update') ?>">
            <?= csrf_field() ?>
            <label>Available subscription option
                <select name="subscription_target" required>
                    <option value="">Select a package or combo</option>
                    <optgroup label="Single-category packages">
                        <?php foreach ($packages as $package): ?>
                            <?php if ((int) $package['stock'] > 0): ?>
                                <?php $isCurrentPackage = (int) ($subscription['package_id'] ?? 0) === (int) $package['id']; ?>
                                <option value="package:<?= (int) $package['id'] ?>" <?= $isCurrentPackage ? 'disabled' : '' ?>>
                                    <?= e($package['package_name']) ?> — &pound;<?= number_format((float) $package['price'], 2) ?> — <?= (int) $package['stock'] ?> available<?= $isCurrentPackage ? ' — Current plan' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="DoublePackage and TriplePackage combos">
                        <?php foreach ($deals as $deal): ?>
                            <?php if ((int) $deal['stock'] > 0): ?>
                                <?php $isCurrentDeal = (int) ($subscription['deal_id'] ?? 0) === (int) $deal['id']; ?>
                                <option value="deal:<?= (int) $deal['id'] ?>" <?= $isCurrentDeal ? 'disabled' : '' ?>>
                                    <?= e($deal['deal_name']) ?> (<?= e($deal['deal_type']) ?>) — &pound;<?= number_format((float) $deal['price'], 2) ?> — <?= e(implode(' + ', array_column($deal['packages'] ?? [], 'package_name'))) ?><?= $isCurrentDeal ? ' — Current plan' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </label>
            <button class="btn primary" type="submit" data-confirm="Change to the selected plan now? The new plan will become active immediately.">Confirm and activate new plan</button>
        </form>
    </div>
</section>

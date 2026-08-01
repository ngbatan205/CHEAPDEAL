<section class="admin-hero">
    <div>
        <p class="eyebrow">Promotion management</p>
        <h1>Offers</h1>
        <p>Create and maintain the promotion codes shown to customers. Archived and expired offers remain in operational history but cannot be redeemed.</p>
    </div>
    <a class="btn primary" href="<?= url('/crm/offer/add') ?>">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Add offer
    </a>
</section>

<section class="admin-section" aria-labelledby="offer-records-title">
    <div class="offer-admin-summary" aria-label="Offer status summary">
        <div><span class="status-pill is-success">Active</span><strong><?= (int) $counts['active'] ?></strong><small>Visible and redeemable</small></div>
        <div><span class="status-pill is-warning">Expired</span><strong><?= (int) $counts['expired'] ?></strong><small>Past the expiry date</small></div>
        <div><span class="status-pill is-muted">Archived</span><strong><?= (int) $counts['archived'] ?></strong><small>Retained for history</small></div>
    </div>

    <div class="admin-subsection-heading">
        <div>
            <p class="eyebrow">Promotion records</p>
            <h2 id="offer-records-title">All offers</h2>
        </div>
        <span class="status-pill is-muted"><?= count($offers) ?> record<?= count($offers) === 1 ? '' : 's' ?></span>
    </div>

    <div class="workflow-callout offer-policy-note">
        <i class="bi bi-shield-check" aria-hidden="true"></i>
        <div>
            <strong>Server-checked promotion lifecycle</strong>
            <p>Customers only see active, unexpired codes. Checkout verifies the same conditions again before calculating a discount.</p>
        </div>
    </div>

    <div class="table-wrap offer-admin-table">
        <table>
            <caption class="visually-hidden">Offer management records</caption>
            <thead>
                <tr>
                    <th scope="col">Code</th>
                    <th scope="col">Customer description</th>
                    <th scope="col">Discount</th>
                    <th scope="col">Expiry</th>
                    <th scope="col">Uses</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offers as $offer): ?>
                    <?php
                    $isArchived = (int) ($offer['is_active'] ?? 1) !== 1;
                    $isExpired = !$isArchived
                        && !empty($offer['expiry_date'])
                        && $offer['expiry_date'] < date('Y-m-d');
                    $statusLabel = $isArchived ? 'Archived' : ($isExpired ? 'Expired' : 'Active');
                    $statusClass = $isArchived ? 'is-muted' : ($isExpired ? 'is-warning' : 'is-success');
                    ?>
                    <tr>
                        <td><code class="offer-code-chip"><?= e($offer['code']) ?></code></td>
                        <td class="offer-description-cell"><?= e($offer['description']) ?></td>
                        <td><strong><?= (int) $offer['discount_percent'] ?>%</strong></td>
                        <td>
                            <?php if (!empty($offer['expiry_date'])): ?>
                                <time datetime="<?= e($offer['expiry_date']) ?>"><?= e(date('d M Y', strtotime($offer['expiry_date']))) ?></time>
                            <?php else: ?>
                                <span class="text-muted">No expiry</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) ($offer['usage_count'] ?? 0) ?></td>
                        <td><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                        <td>
                            <div class="crm-table-actions">
                                <a class="btn small" href="<?= url('/crm/offer/edit?id=' . (int) $offer['id']) ?>">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                                </a>
                                <form method="post" action="<?= url($isArchived ? '/crm/offer/reactivate' : '/crm/offer/archive') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $offer['id'] ?>">
                                    <button
                                        class="btn small<?= $isArchived ? '' : ' package-delete-button' ?>"
                                        type="submit"
                                        <?php if (!$isArchived): ?>data-confirm="Archive <?= e($offer['code']) ?>? Customers will no longer see or redeem it, but its usage history will be retained."<?php endif; ?>
                                    ><?= $isArchived ? 'Reactivate' : 'Archive' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$offers): ?>
                    <tr><td colspan="7">No offers have been created. Use “Add offer” to publish the first promotion.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="admin-hero">
    <div>
        <p class="eyebrow"><?= $canManageCatalogue ? 'Catalogue management' : 'Catalogue reference' ?></p>
        <h1>Packages and combos</h1>
        <p>
            <?= $canManageCatalogue
                ? 'Create and maintain customer-facing plans. Archived entries remain available for historical orders.'
                : 'Check current pricing, allowances, availability and status while helping customers.' ?>
        </p>
    </div>
    <div class="page-actions">
        <?php if ($canManageCatalogue): ?>
            <a class="btn primary" href="<?= url('/crm/package/add') ?>">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Add package
            </a>
            <a class="btn" href="<?= url('/crm/package/add?kind=deal') ?>">
                <i class="bi bi-box2-heart" aria-hidden="true"></i> Add combo
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="admin-section" aria-labelledby="packages-title">
    <div class="admin-subsection-heading">
        <h2 id="packages-title">Packages</h2>
        <span class="status-pill is-muted"><?= count($packages) ?> records</span>
    </div>
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">Package catalogue records</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th><th scope="col">Name</th><th scope="col">Category</th>
                    <th scope="col">Allowances</th><th scope="col">Stock</th><th scope="col">Monthly price</th>
                    <th scope="col">Status</th><th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                    <?php $packageActive = (int) ($package['is_active'] ?? 1) === 1; ?>
                    <tr>
                        <td>#<?= (int) $package['id'] ?></td>
                        <td><strong><?= e($package['package_name']) ?></strong></td>
                        <td><?= e($package['category']) ?></td>
                        <td><?= (int) $package['minutes'] ?> min · <?= (int) $package['sms'] ?> SMS · <?= (int) $package['data_gb'] ?>GB</td>
                        <td><?= (int) $package['stock'] ?></td>
                        <td>&pound;<?= number_format((float) $package['price'], 2) ?></td>
                        <td><span class="status-pill <?= $packageActive ? 'is-success' : 'is-muted' ?>"><?= $packageActive ? 'Active' : 'Archived' ?></span></td>
                        <td>
                            <?php if ($canManageCatalogue): ?>
                                <div class="crm-table-actions">
                                    <a class="btn small" href="<?= url('/crm/package/edit?kind=package&id=' . (int) $package['id']) ?>">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                                    </a>
                                    <form method="post" action="<?= url($packageActive ? '/crm/package/delete' : '/crm/package/reactivate') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="kind" value="package">
                                        <input type="hidden" name="id" value="<?= (int) $package['id'] ?>">
                                        <button
                                            class="btn small<?= $packageActive ? ' package-delete-button' : '' ?>"
                                            type="submit"
                                            <?php if ($packageActive): ?>data-confirm="Archive this package? It will be hidden from new orders but retained in historical records."<?php endif; ?>
                                        ><?= $packageActive ? 'Archive' : 'Reactivate' ?></button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="status-pill is-muted"><i class="bi bi-eye" aria-hidden="true"></i> View only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($packages)): ?><tr><td colspan="8">No package records are available.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-subsection">
        <div class="admin-subsection-heading">
            <h2 id="combos-title">Combos</h2>
            <span class="status-pill is-muted"><?= count($deals) ?> records</span>
        </div>
        <div class="table-wrap">
            <table>
                <caption class="visually-hidden">Combo catalogue records</caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th><th scope="col">Name</th><th scope="col">Type</th>
                        <th scope="col">Included packages</th><th scope="col">Stock</th><th scope="col">Monthly price</th>
                        <th scope="col">Status</th><th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deals as $deal): ?>
                        <?php $dealActive = (int) ($deal['is_active'] ?? 1) === 1; ?>
                        <tr>
                            <td>#<?= (int) $deal['id'] ?></td>
                            <td><strong><?= e($deal['deal_name']) ?></strong></td>
                            <td><?= e($deal['deal_type'] === 'TriplePackage' ? 'Triple' : 'Double') ?></td>
                            <td><?= e(implode(', ', array_column($deal['packages'] ?? [], 'package_name'))) ?></td>
                            <td><?= (int) $deal['stock'] ?></td>
                            <td>&pound;<?= number_format((float) $deal['price'], 2) ?></td>
                            <td><span class="status-pill <?= $dealActive ? 'is-success' : 'is-muted' ?>"><?= $dealActive ? 'Active' : 'Archived' ?></span></td>
                            <td>
                                <?php if ($canManageCatalogue): ?>
                                    <div class="crm-table-actions">
                                        <a class="btn small" href="<?= url('/crm/package/edit?kind=deal&id=' . (int) $deal['id']) ?>">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i> Edit
                                        </a>
                                        <form method="post" action="<?= url($dealActive ? '/crm/package/delete' : '/crm/package/reactivate') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="kind" value="deal">
                                            <input type="hidden" name="id" value="<?= (int) $deal['id'] ?>">
                                            <button
                                                class="btn small<?= $dealActive ? ' package-delete-button' : '' ?>"
                                                type="submit"
                                                <?php if ($dealActive): ?>data-confirm="Archive this combo? It will be hidden from new orders but retained in historical records."<?php endif; ?>
                                            ><?= $dealActive ? 'Archive' : 'Reactivate' ?></button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="status-pill is-muted"><i class="bi bi-eye" aria-hidden="true"></i> View only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($deals)): ?><tr><td colspan="8">No combo records are available.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

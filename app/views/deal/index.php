<?php
$dealImageUrl = function (array $deal): string {
    $candidates = [
        $deal['image'] ?? '',
        $deal['packages'][0]['image'] ?? '',
    ];

    foreach ($candidates as $image) {
        $image = trim((string) $image);
        if ($image === '') {
            continue;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        $relativePath = ltrim($image, '/');
        if (is_file(dirname(APP_ROOT) . '/public/' . $relativePath)) {
            return url('/' . $relativePath);
        }

        $legacyPath = 'images/' . basename($relativePath);
        if (is_file(dirname(APP_ROOT) . '/public/' . $legacyPath)) {
            return url('/' . $legacyPath);
        }
    }

    return url('/images/double-starter.png');
};
?>

<section class="combo-hero">
    <div>
        <p class="eyebrow">Better together</p>
        <h1>Combo plans, thoughtfully bundled.</h1>
        <p>
            Combine services from different categories in one curated deal.
            Every combo below is loaded directly from our current deals catalogue.
        </p>
        <div class="combo-benefits">
            <span><i class="bi bi-check2-circle"></i> One clear monthly price</span>
            <span><i class="bi bi-check2-circle"></i> Hand-picked plans</span>
            <span><i class="bi bi-check2-circle"></i> Built-in savings</span>
        </div>
    </div>
    <div class="combo-hero-saving">
        <i class="bi bi-box2-heart-fill"></i>
        <span>Save with</span>
        <strong>2 or 3</strong>
        <small>services combined</small>
    </div>
</section>

<section class="section combo-catalogue">
    <div class="section-heading combo-heading">
        <div>
            <p class="eyebrow">Combo catalogue</p>
            <h2>Choose your ideal bundle</h2>
            <p>Compare live prices and the plans included in every deal.</p>
        </div>
        <nav class="combo-tabs" aria-label="Filter combo plans">
            <a class="<?= $selectedType === '' ? 'is-active' : '' ?>" href="<?= url('/combos') ?>">All combos</a>
            <a class="<?= $selectedType === 'double' ? 'is-active' : '' ?>" href="<?= url('/combos?type=double') ?>">Double</a>
            <a class="<?= $selectedType === 'triple' ? 'is-active' : '' ?>" href="<?= url('/combos?type=triple') ?>">Triple</a>
        </nav>
    </div>

    <div class="combo-grid">
        <?php foreach ($deals as $deal): ?>
            <?php
            $isTriple = $deal['deal_type'] === 'TriplePackage';
            $saving = (float) $deal['normal_price'] - (float) $deal['price'];
            $savingPercent = (int) $deal['discount_percent'];
            ?>
            <article class="combo-card<?= $isTriple ? ' combo-triple' : '' ?>">
                <div class="combo-card-media">
                    <img src="<?= e($dealImageUrl($deal)) ?>" alt="<?= e($deal['deal_name']) ?>">
                    <span class="combo-type-badge">
                        <i class="bi <?= $isTriple ? 'bi-3-circle-fill' : 'bi-2-circle-fill' ?>"></i>
                        <?= $isTriple ? 'Triple package' : 'Double package' ?>
                    </span>
                    <span class="combo-stock"><?= (int) $deal['stock'] ?> available</span>
                </div>

                <div class="combo-card-body">
                    <div class="combo-card-title">
                        <div>
                            <span>Save <?= (int) $savingPercent ?>%</span>
                            <h3><?= e($deal['deal_name']) ?></h3>
                        </div>
                        <div class="combo-price">
                            <del>&pound;<?= number_format((float) $deal['normal_price'], 2) ?></del>
                            <strong>&pound;<?= number_format((float) $deal['price'], 2) ?></strong>
                            <small>per month</small>
                        </div>
                    </div>

                    <p class="combo-description"><?= e($deal['description']) ?></p>

                    <div class="included-plans">
                        <span class="included-label">Plans included</span>
                        <?php foreach ($deal['packages'] as $package): ?>
                            <div class="included-plan">
                                <span class="included-icon">
                                    <i class="bi <?= match ($package['category']) {
                                        'Mobile' => 'bi-phone',
                                        'Broadband' => 'bi-router',
                                        'Tablet' => 'bi-tablet',
                                        default => 'bi-box'
                                    } ?>"></i>
                                </span>
                                <div>
                                    <small><?= e($package['category']) ?></small>
                                    <strong><?= e($package['package_name']) ?></strong>
                                </div>
                                <span><?= (int) $package['data_gb'] ?>GB</span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="combo-saving-line">
                        <span>You save every month</span>
                        <strong>&pound;<?= number_format($saving, 2) ?></strong>
                    </div>

                    <div class="combo-actions">
                        <a class="btn small" href="<?= url('/combo?id=' . (int) $deal['id']) ?>">
                            Details
                        </a>
                        <form method="post" action="<?= url('/cart/add-deal') ?>" data-cart-add>
                            <?= csrf_field() ?>
                            <input type="hidden" name="deal_id" value="<?= (int) $deal['id'] ?>">
                            <input type="hidden" name="return_to" value="/combos">
                            <button class="btn small" type="submit">
                                <i class="bi bi-cart-plus"></i> Add to cart
                            </button>
                        </form>
                        <a class="btn primary small" href="<?= url('/payment?deal_id=' . (int) $deal['id']) ?>">
                            Order
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$deals): ?>
            <div class="empty-state">
                <i class="bi bi-box2"></i>
                <h2>No combos found</h2>
                <p>There are currently no combo deals in this category.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

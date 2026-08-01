<?php if (!$deal): ?>
    <section class="section narrow">
        <h1>Combo not found</h1>
        <p>This combo does not exist or is no longer available.</p>
        <a class="btn primary" href="<?= url('/combos') ?>">Back to combos</a>
    </section>
<?php else: ?>
    <?php
    $isTriple = $deal['deal_type'] === 'TriplePackage';
    $saving = (float) $deal['normal_price'] - (float) $deal['price'];
    $coverUrl = '';
    foreach ([$deal['image'] ?? '', $deal['packages'][0]['image'] ?? ''] as $coverImage) {
        $coverImage = trim((string) $coverImage);
        if ($coverImage === '') {
            continue;
        }
        if (str_starts_with($coverImage, 'http://') || str_starts_with($coverImage, 'https://')) {
            $coverUrl = $coverImage;
            break;
        }

        $relativePath = ltrim($coverImage, '/');
        if (is_file(dirname(APP_ROOT) . '/public/' . $relativePath)) {
            $coverUrl = url('/' . $relativePath);
            break;
        }

        $legacyPath = 'images/' . basename($relativePath);
        if (is_file(dirname(APP_ROOT) . '/public/' . $legacyPath)) {
            $coverUrl = url('/' . $legacyPath);
            break;
        }
    }
    $coverUrl = $coverUrl ?: url('/images/double-starter.png');
    ?>

    <section class="combo-detail-hero">
        <div class="combo-detail-media">
            <img src="<?= e($coverUrl) ?>" alt="<?= e($deal['deal_name']) ?>">
            <span><?= $isTriple ? 'Triple package' : 'Double package' ?></span>
        </div>

        <div class="combo-detail-copy">
            <a class="checkout-back" href="<?= url('/combos') ?>">
                <i class="bi bi-arrow-left"></i> Back to combos
            </a>
            <p class="eyebrow">Save <?= (int) $deal['discount_percent'] ?>%</p>
            <h1><?= e($deal['deal_name']) ?></h1>
            <p><?= e($deal['description']) ?></p>

            <div class="combo-detail-price">
                <div>
                    <del>&pound;<?= number_format((float) $deal['normal_price'], 2) ?></del>
                    <strong>&pound;<?= number_format((float) $deal['price'], 2) ?></strong>
                    <span>per month</span>
                </div>
                <div>
                    <small>You save</small>
                    <strong>&pound;<?= number_format($saving, 2) ?></strong>
                </div>
            </div>

            <div class="combo-detail-actions">
                <form method="post" action="<?= url('/cart/add-deal') ?>" data-cart-add>
                    <?= csrf_field() ?>
                    <input type="hidden" name="deal_id" value="<?= (int) $deal['id'] ?>">
                    <input type="hidden" name="return_to" value="/combo?id=<?= (int) $deal['id'] ?>">
                    <button class="btn" type="submit">
                        <i class="bi bi-cart-plus"></i> Add to cart
                    </button>
                </form>
                <a class="btn primary" href="<?= url('/payment?deal_id=' . (int) $deal['id']) ?>">Order now</a>
            </div>
        </div>
    </section>

    <section class="section combo-detail-plans">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Included services</p>
                <h2>Everything in this combo</h2>
            </div>
            <span class="stock-pill"><?= (int) $deal['stock'] ?> combos available</span>
        </div>

        <div class="combo-detail-grid">
            <?php foreach ($deal['packages'] as $package): ?>
                <article>
                    <span><?= e($package['category']) ?></span>
                    <h3><?= e($package['package_name']) ?></h3>
                    <p><?= e($package['description']) ?></p>
                    <ul>
                        <li><strong><?= (int) $package['data_gb'] ?>GB</strong> data</li>
                        <li><strong><?= (int) $package['minutes'] ?></strong> minutes</li>
                        <li><strong><?= (int) $package['sms'] ?></strong> SMS</li>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

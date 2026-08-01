<?php if (!$package): ?>

<section class="section narrow">
    <h1>Package not found</h1>

    <p>
        The selected package does not exist
        or is no longer available.
    </p>

    <a class="btn primary" href="<?= url('/packages') ?>">
        Back to packages
    </a>
</section>

<?php else: ?>

<?php
$categoryName = match ($package['category']) {
    'Mobile' => 'MobileOnly',
    'Broadband' => 'BroadbandOnly',
    'Tablet' => 'TabletOnly',
    default => $package['category']
};
?>

<section class="detail-hero">
    <img
        src="<?= e($package['image']) ?>"
        alt="<?= e($package['package_name']) ?>"
    >

    <div>
        <p class="eyebrow">
            <?= e($categoryName) ?>
            ·
            <?= (int) $package['stock'] ?> in stock
        </p>

        <h1><?= e($package['package_name']) ?></h1>

        <p><?= e($package['description']) ?></p>

        <strong class="big-price">
            £<?= number_format(
                (float) $package['price'],
                2
            ) ?>
            per month
        </strong>

        <div class="hero-actions">
            <form method="post" action="<?= url('/cart/add') ?>" data-cart-add>
                <?= csrf_field() ?>
                <input type="hidden" name="package_id" value="<?= (int) $package['id'] ?>">
                <input type="hidden" name="return_to" value="/package?id=<?= (int) $package['id'] ?>">
                <button class="btn" type="submit">
                    <i class="bi bi-cart-plus"></i> Add to cart
                </button>
            </form>

            <a
                class="btn primary"
                href="<?= url(
                    '/payment?package_id=' . $package['id']
                ) ?>"
            >
                Order now
            </a>

            <a
                class="btn ghost"
                href="<?= url(
                    '/enquiry?package_id=' . $package['id']
                ) ?>"
            >
                Enquire
            </a>
        </div>
    </div>
</section>

<section class="section narrow">
    <h2>Package allowance</h2>

    <ul class="check-list">
        <li>
            <?= (int) $package['minutes'] ?> minutes
        </li>

        <li>
            <?= (int) $package['sms'] ?> SMS
        </li>

        <li>
            <?= (int) $package['data_gb'] ?>GB data
        </li>

        <li>
            <?= (int) $package['stock'] ?> available
        </li>
    </ul>

    <a class="btn" href="<?= url('/packages') ?>">
        Back to packages
    </a>
</section>

<?php endif; ?>

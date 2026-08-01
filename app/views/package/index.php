<?php
// Support both external image URLs and local images
$packageImageUrl = function (?string $image): string {
    $image = trim((string) $image);

    // External image URL
    if (
        str_starts_with($image, 'http://') ||
        str_starts_with($image, 'https://')
    ) {
        return $image;
    }

    // Local image
    // Example:
    // images/mobile-max.png
    return url('/' . ltrim($image, '/'));
};

?>


<section class="hero">

    <div>

        <p class="eyebrow">
            CheapDeals network
        </p>

        <h1>
            Find the right plan without decoding the fine print.
        </h1>

        <p>
            Browse mobile, broadband, and tablet packages by allowance,
            price, and category. Order online, apply offers, and manage
            everything from your account.
        </p>

        <div class="hero-actions">

            <a
                class="btn primary"
                href="#compare-packages"
            >
                Compare plans
            </a>

            <a
                class="btn ghost"
                href="<?= url('/enquiry') ?>"
            >
                Ask a question
            </a>

        </div>

    </div>


    <div class="hero-panel">

        <span>
            Popular bundle
        </span>

        <strong>
            80GB
        </strong>

        <small>
            High-data mobile plans, full-fibre broadband options,
            and tablet data packages in one catalogue.
        </small>

    </div>

</section>



<section
    class="trust-strip"
    aria-label="Service highlights"
>

    <div>

        <i class="bi bi-receipt" aria-hidden="true"></i>

        <strong>
            Clear monthly pricing
        </strong>

        <span>
            See plan costs and discounts before you confirm an order.
        </span>

    </div>


    <div>

        <i class="bi bi-shield-check" aria-hidden="true"></i>

        <strong>
            Secure card checks
        </strong>

        <span>
            Card details are verified securely and only masked information is retained.
        </span>

    </div>


    <div>

        <i class="bi bi-chat-square-heart" aria-hidden="true"></i>

        <strong>
            Support that follows through
        </strong>

        <span>
            Send a question and review the response from your account messages.
        </span>

    </div>

</section>



<?php if (!empty($featured)): ?>

<section class="section">


    <div class="section-heading">

        <div>

            <p class="eyebrow">
                Popular this month
            </p>

            <h2>
                Featured plans
            </h2>

        </div>


        <a href="<?= url('/offers') ?>">
            View offers
        </a>

    </div>



    <div class="deal-grid">


        <?php foreach ($featured as $package): ?>


            <article class="deal-card feature">


                <!-- Package image -->
                <img
                    src="<?= e(
                        $packageImageUrl(
                            $package['image'] ?? ''
                        )
                    ) ?>"
                    alt="<?= e($package['package_name']) ?>"
                >


                <div>


                    <p class="eyebrow">

                        <?= e($package['category']) ?>

                        /

                        <?= (int) $package['data_gb'] ?>GB data

                    </p>


                    <h3>

                        <?= e($package['package_name']) ?>

                    </h3>


                    <p>

                        <?= e($package['description']) ?>

                    </p>



                    <ul class="mini-specs">

                        <li>

                            <?= (int) $package['minutes'] ?> min

                        </li>


                        <li>

                            <?= (int) $package['sms'] ?> SMS

                        </li>


                        <li>

                            <?= (int) $package['stock'] > 0 ? 'Available now' : 'Unavailable' ?>

                        </li>

                    </ul>



                    <div class="card-row">


                        <strong>

                            £<?= number_format(
                                (float) $package['price'],
                                2
                            ) ?>/mo

                        </strong>


                        <a
                            class="btn small"
                            href="<?= url(
                                '/package?id=' . $package['id']
                            ) ?>"
                        >
                            Details
                        </a>


                    </div>

                </div>

            </article>


        <?php endforeach; ?>


    </div>

</section>

<?php endif; ?>



<section
    class="section"
    id="packages"
>


    <div class="section-heading">


        <div>

            <p class="eyebrow">
                Catalogue
            </p>

            <h2>
                Compare packages
            </h2>

        </div>


        <span>

            <?= count($packages) ?> available

        </span>


    </div>



    <form
        class="filter-bar"
        method="get"
        action="<?= url('/packages') ?>"
    >


        <label>

            <span>
                Search
            </span>

            <input
                name="q"
                value="<?= e($search ?? '') ?>"
                placeholder="Plan name or feature"
            >

        </label>



        <label>

            <span>
                Category
            </span>


            <select name="category">


                <option value="">
                    All categories
                </option>


                <?php foreach ($categories as $category): ?>


                    <option

                        value="<?= e($category) ?>"

                        <?= ($selectedCategory ?? '') === $category
                            ? 'selected'
                            : '' ?>

                    >

                        <?= e($category) ?>

                    </option>


                <?php endforeach; ?>


            </select>


        </label>



        <button
            class="btn primary"
            type="submit"
        >
            Filter
        </button>



        <a
            class="btn"
            href="<?= url('/packages') ?>"
        >
            Reset
        </a>

    </form>

    <div class="package-compare-intro" id="compare-packages">
        <div>
            <span class="package-compare-icon"><i class="bi bi-columns-gap"></i></span>
            <div>
                <strong>Compare package price and allowances</strong>
                <p>Select any two packages below to compare them side by side.</p>
            </div>
        </div>
        <span data-compare-count>0 of 2 selected</span>
    </div>



    <div class="package-list plan-grid">


        <?php foreach ($packages as $package): ?>


            <?php

            $categoryName = match ($package['category']) {

                'Mobile' => 'MobileOnly',

                'Broadband' => 'BroadbandOnly',

                'Tablet' => 'TabletOnly',

                default => $package['category']

            };

            ?>


            <article class="package-row plan-card">


                <!-- Package image -->
                <img
                    src="<?= e(
                        $packageImageUrl(
                            $package['image'] ?? ''
                        )
                    ) ?>"
                    alt="<?= e($package['package_name']) ?>"
                >



                <div>


                    <p class="eyebrow">

                        <?= e($categoryName) ?>

                    </p>



                    <h3>

                        <?= e($package['package_name']) ?>

                    </h3>



                    <p>

                        <?= e($package['description']) ?>

                    </p>



                    <ul class="spec-list">


                        <li>

                            <strong>

                                <?= (int) $package['data_gb'] ?>GB

                            </strong>

                            <span>
                                Data
                            </span>

                        </li>



                        <li>

                            <strong>

                                <?= (int) $package['minutes'] ?>

                            </strong>

                            <span>
                                Minutes
                            </span>

                        </li>



                        <li>

                            <strong>

                                <?= (int) $package['sms'] ?>

                            </strong>

                            <span>
                                SMS
                            </span>

                        </li>


                    </ul>

                    <div class="package-inline-price">
                        <span>per month</span>
                        <strong>&pound;<?= number_format((float) $package['price'], 2) ?></strong>
                    </div>

                    <button
                        class="package-compare-toggle"
                        type="button"
                        aria-pressed="false"
                        data-compare-package
                        data-package-id="<?= (int) $package['id'] ?>"
                        data-package-name="<?= e($package['package_name']) ?>"
                        data-package-category="<?= e($package['category']) ?>"
                        data-package-price="<?= e(number_format((float) $package['price'], 2, '.', '')) ?>"
                        data-package-data="<?= (int) $package['data_gb'] ?>"
                        data-package-minutes="<?= (int) $package['minutes'] ?>"
                        data-package-sms="<?= (int) $package['sms'] ?>"
                    >
                        <i class="bi bi-plus-circle"></i>
                        <span>Add to compare</span>
                    </button>

                </div>



                <div class="price-box">

                    <a
                        class="btn small"
                        href="<?= url(
                            '/package?id=' . $package['id']
                        ) ?>"
                    >
                        Details
                    </a>



                    <form method="post" action="<?= url('/cart/add') ?>" data-cart-add>
                        <?= csrf_field() ?>
                        <input type="hidden" name="package_id" value="<?= (int) $package['id'] ?>">
                        <input type="hidden" name="return_to" value="/packages#packages">
                        <button class="btn small cart-add-button" type="submit">
                            <i class="bi bi-cart-plus"></i> Add to cart
                        </button>
                    </form>

                    <a
                        class="btn primary small"
                        href="<?= url(
                            '/payment?package_id=' . $package['id']
                        ) ?>"
                    >
                        Order
                    </a>

                </div>


            </article>


        <?php endforeach; ?>



        <?php if (!$packages): ?>


            <div class="empty-state">

                No packages match your search.

            </div>


        <?php endif; ?>


    </div>


</section>

<div class="package-compare-dock" data-compare-dock hidden>
    <div class="package-compare-slots">
        <span data-compare-slot="0">Choose first package</span>
        <i class="bi bi-arrow-left-right"></i>
        <span data-compare-slot="1">Choose second package</span>
    </div>
    <div class="package-compare-actions">
        <button class="btn small" type="button" data-compare-clear>Clear</button>
        <button class="btn primary small" type="button" data-compare-open disabled>
            Compare selected
        </button>
    </div>
</div>

<dialog class="package-compare-dialog" data-compare-dialog>
    <div class="package-compare-dialog-header">
        <div>
            <p class="eyebrow">Side-by-side comparison</p>
            <h2>Package price and allowances</h2>
        </div>
        <button type="button" data-compare-close aria-label="Close comparison">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div data-compare-table></div>
</dialog>

<script>
(() => {
    const packageButtons = [...document.querySelectorAll('[data-compare-package]')];
    const dock = document.querySelector('[data-compare-dock]');
    const countLabels = [...document.querySelectorAll('[data-compare-count]')];
    const slots = [...document.querySelectorAll('[data-compare-slot]')];
    const openButton = document.querySelector('[data-compare-open]');
    const clearButton = document.querySelector('[data-compare-clear]');
    const dialog = document.querySelector('[data-compare-dialog]');
    const tableHost = document.querySelector('[data-compare-table]');
    const selected = [];

    function packageFrom(button) {
        return {
            id: button.dataset.packageId,
            name: button.dataset.packageName,
            category: button.dataset.packageCategory,
            price: button.dataset.packagePrice,
            data: button.dataset.packageData,
            minutes: button.dataset.packageMinutes,
            sms: button.dataset.packageSms,
            button
        };
    }

    function refreshCompare() {
        packageButtons.forEach(button => {
            const isSelected = selected.some(item => item.id === button.dataset.packageId);
            const isUnavailable = selected.length === 2 && !isSelected;
            button.classList.toggle('is-selected', isSelected);
            button.classList.toggle('is-unavailable', isUnavailable);
            button.disabled = isUnavailable;
            button.setAttribute('aria-pressed', String(isSelected));
            button.querySelector('i').className = isSelected
                ? 'bi bi-check-circle-fill'
                : 'bi bi-plus-circle';
            button.querySelector('span').textContent = isSelected
                ? 'Selected for comparison'
                : 'Add to compare';
        });

        countLabels.forEach(label => {
            label.textContent = `${selected.length} of 2 selected`;
        });
        slots[0].textContent = selected[0]?.name || 'Choose first package';
        slots[1].textContent = selected[1]?.name || 'Choose second package';
        openButton.disabled = selected.length !== 2;
        dock.hidden = selected.length === 0;
    }

    function comparisonRow(label, first, second, highlight = false) {
        const row = document.createElement('div');
        row.className = `package-comparison-row${highlight ? ' is-highlighted' : ''}`;

        const heading = document.createElement('strong');
        heading.textContent = label;
        row.append(heading);

        [first, second].forEach(value => {
            const cell = document.createElement('span');
            cell.textContent = value;
            row.append(cell);
        });
        return row;
    }

    function openComparison() {
        if (selected.length !== 2) return;
        const [first, second] = selected;
        tableHost.replaceChildren(
            comparisonRow('Package', first.name, second.name),
            comparisonRow('Category', first.category, second.category),
            comparisonRow('Monthly price', `£${first.price}`, `£${second.price}`, true),
            comparisonRow('Data', `${first.data}GB`, `${second.data}GB`),
            comparisonRow('Minutes', first.minutes, second.minutes),
            comparisonRow('SMS', first.sms, second.sms)
        );
        dialog.showModal();
    }

    packageButtons.forEach(button => button.addEventListener('click', () => {
        const index = selected.findIndex(item => item.id === button.dataset.packageId);
        if (index >= 0) {
            selected.splice(index, 1);
        } else if (selected.length < 2) {
            selected.push(packageFrom(button));
        }
        refreshCompare();
    }));

    clearButton.addEventListener('click', () => {
        selected.splice(0);
        refreshCompare();
    });
    openButton.addEventListener('click', openComparison);
    dialog.querySelector('[data-compare-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });

    refreshCompare();
})();
</script>

<section class="section offer-page">
    <header class="section-heading offer-page-heading">
        <div>
            <p class="eyebrow">Current promotions</p>
            <h1>Offers that make a good plan even better</h1>
            <p>Choose an active code and enter it during checkout for an eligible single-category order.</p>
        </div>
        <a class="btn primary" href="<?= url('/packages') ?>">
            Compare plans <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
    </header>

    <?php if ($offers): ?>
        <div class="offer-grid public-offer-grid">
            <?php foreach ($offers as $offer): ?>
                <article class="offer-card public-offer-card">
                    <div class="public-offer-topline">
                        <span class="offer-code-chip"><?= e($offer['code']) ?></span>
                        <span class="status-pill is-success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Active</span>
                    </div>
                    <div class="public-offer-saving">
                        <strong><?= (int) $offer['discount_percent'] ?>%</strong>
                        <span>off after the app discount</span>
                    </div>
                    <p><?= e($offer['description']) ?></p>
                    <footer>
                        <span><i class="bi bi-calendar3" aria-hidden="true"></i>
                            <?php if (!empty($offer['expiry_date'])): ?>
                                Valid through <time datetime="<?= e($offer['expiry_date']) ?>"><?= e(date('d M Y', strtotime($offer['expiry_date']))) ?></time>
                            <?php else: ?>
                                No expiry date
                            <?php endif; ?>
                        </span>
                        <a href="<?= url('/packages') ?>">Choose a plan</a>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state offer-empty-state">
            <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
            <h2>No offers are available right now</h2>
            <p>You can still receive the automatic 15% app-order promotion at checkout.</p>
            <a class="btn primary" href="<?= url('/packages') ?>">Browse plans</a>
        </div>
    <?php endif; ?>

    <aside class="offer-terms" aria-labelledby="offer-terms-title">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <div>
            <h2 id="offer-terms-title">How offer codes work</h2>
            <p>The automatic 15% app-order promotion is calculated first. One active offer code can then reduce the remaining balance on a single-category order. Codes are checked again when payment is submitted.</p>
        </div>
    </aside>
</section>

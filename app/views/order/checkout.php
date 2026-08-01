<section class="section cart-confirm">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Your cart</p>
            <h1>Confirm your order</h1>
            <p>Review, edit, or remove packages before payment.</p>
        </div>
        <a class="btn ghost" href="<?= url('/packages') ?>">Continue shopping</a>
    </div>

    <?php if (!$items): ?>
        <div class="empty-state">
            <i class="bi bi-cart3"></i>
            <h2>Your cart is empty</h2>
            <p>Add a package and it will appear here.</p>
            <a class="btn primary" href="<?= url('/packages') ?>">Browse packages</a>
        </div>
    <?php else: ?>
        <div class="cart-list">
            <?php foreach ($items as $item): ?>
                <article class="cart-item" data-cart-item>
                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['package_name']) ?>">
                    <div class="cart-item-info">
                        <span><?= e($item['category']) ?></span>
                        <h2><?= e($item['package_name']) ?></h2>
                        <p>&pound;<?= number_format((float) $item['price'], 2) ?> per month</p>
                    </div>
                    <form class="cart-edit" method="post" action="<?= url('/cart/update') ?>" data-cart-update>
                        <?= csrf_field() ?>
                        <input type="hidden" name="package_id" value="<?= (int) $item['id'] ?>">
                        <label>
                            Quantity
                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                max="99"
                                value="<?= e((string) $item['quantity']) ?>"
                                data-unit-price="<?= e((string) $item['price']) ?>"
                                aria-label="Quantity for <?= e($item['package_name']) ?>"
                            >
                        </label>
                        <small class="cart-saving<?= $item['quantity_invalid'] ? ' is-error' : '' ?>" aria-live="polite">
                            <?= $item['quantity_invalid'] ? 'Quantity required' : '' ?>
                        </small>
                    </form>
                    <strong class="cart-line-total">
                        <?= $item['line_total'] === null ? '&mdash;' : '&pound;' . number_format((float) $item['line_total'], 2) ?>
                    </strong>
                    <form method="post" action="<?= url('/cart/remove') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="package_id" value="<?= (int) $item['id'] ?>">
                        <button class="cart-remove" type="submit" aria-label="Remove <?= e($item['package_name']) ?>">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cart-total">
            <div>
                <span>Subtotal</span>
                <strong data-cart-subtotal><?= $hasInvalidQuantity ? '&mdash;' : '&pound;' . number_format((float) $total, 2) ?></strong>
            </div>
            <p>Discounts can be applied on the payment page.</p>
            <p class="cart-validation-message<?= $hasInvalidQuantity ? ' is-visible' : '' ?>" data-cart-warning>
                Please enter a quantity from 1 to 99 for every package.
            </p>
            <a
                class="btn primary<?= $hasInvalidQuantity ? ' is-disabled' : '' ?>"
                href="<?= url('/payment?source=cart') ?>"
                data-cart-payment
                aria-disabled="<?= $hasInvalidQuantity ? 'true' : 'false' ?>"
            >
                Continue to payment <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>
</section>

<?php
$isEdit = !empty($offer['id']);
$statusLabels = [
    'active' => ['Active', 'is-success'],
    'expired' => ['Expired', 'is-warning'],
    'archived' => ['Archived', 'is-muted'],
];
[$statusLabel, $statusClass] = $statusLabels[$offerStatus] ?? $statusLabels['active'];
?>

<section class="package-editor-page offer-editor-page">
    <a class="checkout-back" href="<?= url('/crm/offers') ?>">
        <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to offers
    </a>

    <div class="package-editor-heading">
        <div>
            <p class="eyebrow">Admin promotions</p>
            <h1><?= $isEdit ? 'Edit offer' : 'Add offer' ?></h1>
            <p>Define the customer-facing code, discount and optional expiry date.</p>
        </div>
        <?php if ($isEdit): ?><span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span><?php endif; ?>
    </div>

    <?php if ($errors): ?>
        <div class="editor-errors" role="alert">
            <strong>Please check the form:</strong>
            <ul>
                <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="package-editor-form" method="post" action="<?= url('/crm/offer/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) ($offer['id'] ?? 0) ?>">

        <section class="editor-panel">
            <div class="editor-panel-title">
                <span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                <div>
                    <h2>Offer details</h2>
                    <p>These details are shown on the public Offers page and at checkout.</p>
                </div>
            </div>

            <div class="editor-fields offer-editor-fields">
                <label class="editor-field">
                    <span>Offer code</span>
                    <input
                        name="code"
                        value="<?= e((string) ($offer['code'] ?? '')) ?>"
                        maxlength="20"
                        pattern="[A-Za-z0-9][A-Za-z0-9-]{2,19}"
                        placeholder="WELCOME10"
                        autocomplete="off"
                        spellcheck="false"
                        required
                    >
                    <small>3–20 letters, numbers or hyphens. It is saved in uppercase.</small>
                </label>

                <label class="editor-field">
                    <span>Discount percentage</span>
                    <span class="offer-percent-input">
                        <input type="number" name="discount_percent" min="1" max="90" step="1" value="<?= e((string) ($offer['discount_percent'] ?? 10)) ?>" required>
                        <strong aria-hidden="true">%</strong>
                    </span>
                    <small>Whole percentage from 1 to 90.</small>
                </label>

                <label class="editor-field">
                    <span>Expiry date <em>(optional)</em></span>
                    <input type="date" name="expiry_date" min="<?= e(date('Y-m-d')) ?>" value="<?= e((string) ($offer['expiry_date'] ?? '')) ?>">
                    <small>Leave blank for no expiry. The offer remains valid through this date.</small>
                </label>

                <label class="editor-field editor-field-wide">
                    <span>Customer description</span>
                    <textarea name="description" rows="4" minlength="5" maxlength="255" placeholder="Explain the saving and any important condition." required><?= e((string) ($offer['description'] ?? '')) ?></textarea>
                    <small>Use plain language so customers understand what the code provides.</small>
                </label>
            </div>
        </section>

        <section class="editor-panel offer-rule-panel" aria-labelledby="offer-rule-title">
            <div class="editor-panel-title">
                <span><i class="bi bi-calculator" aria-hidden="true"></i></span>
                <div>
                    <h2 id="offer-rule-title">How checkout applies this offer</h2>
                    <p>The 15% app-order promotion is calculated first. This offer then reduces the remaining balance on a single-category order.</p>
                </div>
            </div>
            <div class="offer-rule-example">
                <span><strong>Example</strong><small>£100 package subtotal</small></span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <span><strong>£85.00</strong><small>after app discount</small></span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <span><strong>Offer applied</strong><small>to the £85.00 balance</small></span>
            </div>
        </section>

        <div class="editor-submit-bar">
            <?php if ($isEdit): ?>
                <button
                    class="btn editor-delete-button<?= $offerStatus === 'archived' ? '' : ' package-delete-button' ?>"
                    type="submit"
                    formaction="<?= url($offerStatus === 'archived' ? '/crm/offer/reactivate' : '/crm/offer/archive') ?>"
                    formmethod="post"
                    <?php if ($offerStatus !== 'archived'): ?>data-confirm="Archive this offer? It will be hidden from customers and rejected at checkout."<?php endif; ?>
                >
                    <i class="bi <?= $offerStatus === 'archived' ? 'bi-arrow-counterclockwise' : 'bi-archive' ?>" aria-hidden="true"></i>
                    <?= $offerStatus === 'archived' ? 'Reactivate offer' : 'Archive offer' ?>
                </button>
            <?php endif; ?>
            <a class="btn" href="<?= url('/crm/offers') ?>">Cancel</a>
            <button class="btn primary" type="submit">
                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                <?= $isEdit ? 'Save changes' : 'Create offer' ?>
            </button>
        </div>
    </form>
</section>

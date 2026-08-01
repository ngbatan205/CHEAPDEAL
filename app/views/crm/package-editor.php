<?php
$isEdit = !empty($item['id']);
$selectedPackageIds = array_map(
    'intval',
    array_column($item['packages'] ?? [], 'id')
);
$currentKind = $kind === 'deal' ? 'deal' : 'package';
$currentImage = trim((string) ($item['image'] ?? ''));
$currentImageUrl = $currentImage === ''
    ? ''
    : (
        str_starts_with($currentImage, 'http://')
        || str_starts_with($currentImage, 'https://')
            ? $currentImage
            : url('/' . ltrim($currentImage, '/'))
    );
?>

<section class="package-editor-page">
    <a class="checkout-back" href="<?= url('/crm/packages') ?>">
        <i class="bi bi-arrow-left"></i> Back to catalogue
    </a>

    <div class="package-editor-heading">
        <div>
            <p class="eyebrow">Admin catalogue</p>
            <h1><?= $isEdit ? 'Edit' : 'Add' ?> <?= $currentKind === 'deal' ? 'combo' : 'package' ?></h1>
            <p>Manage plan details, availability, pricing and included services.</p>
        </div>
        <?php if (!$isEdit): ?>
            <div class="editor-kind-switch" aria-label="Package type">
                <a class="<?= $currentKind === 'package' ? 'is-active' : '' ?>" href="<?= url('/crm/package/add?kind=package') ?>">Single package</a>
                <a class="<?= $currentKind === 'deal' ? 'is-active' : '' ?>" href="<?= url('/crm/package/add?kind=deal') ?>">Combo</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($errors): ?>
        <div class="editor-errors" role="alert">
            <strong>Please check the form:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="package-editor-form" method="post" enctype="multipart/form-data" action="<?= url('/crm/package/save') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="kind" value="<?= e($currentKind) ?>">
        <input type="hidden" name="id" value="<?= (int) ($item['id'] ?? 0) ?>">

        <section class="editor-panel">
            <div class="editor-panel-title">
                <span><i class="bi bi-box-seam"></i></span>
                <div>
                    <h2>Basic information</h2>
                    <p>The main content customers see in the catalogue.</p>
                </div>
            </div>

            <div class="editor-fields">
                <label class="editor-field editor-field-wide">
                    <span><?= $currentKind === 'deal' ? 'Combo' : 'Package' ?> name</span>
                    <input name="name" maxlength="120" required value="<?= e($item[$currentKind === 'deal' ? 'deal_name' : 'package_name'] ?? '') ?>">
                </label>

                <?php if ($currentKind === 'package'): ?>
                    <label class="editor-field">
                        <span>Category</span>
                        <select name="category" required>
                            <?php foreach (['Mobile', 'Broadband', 'Tablet'] as $category): ?>
                                <option value="<?= e($category) ?>" <?= ($item['category'] ?? '') === $category ? 'selected' : '' ?>>
                                    <?= e($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="editor-field">
                        <span>Monthly price (&pound;)</span>
                        <input type="number" name="price" min="0.01" step="0.01" required value="<?= e((string) ($item['price'] ?? '')) ?>">
                    </label>
                <?php else: ?>
                    <label class="editor-field">
                        <span>Combo type</span>
                        <select id="deal-type" name="deal_type" required>
                            <option value="DoublePackage" <?= ($item['deal_type'] ?? '') === 'DoublePackage' ? 'selected' : '' ?>>Double package (2 categories)</option>
                            <option value="TriplePackage" <?= ($item['deal_type'] ?? '') === 'TriplePackage' ? 'selected' : '' ?>>Triple package (3 categories)</option>
                        </select>
                    </label>
                    <div class="editor-auto-price">
                        <span>Combo pricing</span>
                        <strong id="combo-price-preview">Select included packages</strong>
                        <small>Calculated automatically with 15% discount.</small>
                    </div>
                <?php endif; ?>

                <label class="editor-field editor-field-wide">
                    <span>Description</span>
                    <textarea name="description" rows="4" required><?= e($item['description'] ?? '') ?></textarea>
                </label>

                <label class="editor-field">
                    <span>Stock</span>
                    <input type="number" name="stock" min="0" step="1" required value="<?= e((string) ($item['stock'] ?? 100)) ?>">
                </label>
                <div class="editor-field editor-image-field">
                    <span><?= $currentKind === 'deal' ? 'Combo' : 'Package' ?> image</span>
                    <label class="editor-image-upload" id="image-drop-zone" for="image-file">
                        <input
                            id="image-file"
                            type="file"
                            name="image_file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            <?= !$isEdit && $currentKind === 'package' ? 'required' : '' ?>
                        >
                        <span class="editor-image-preview">
                            <img
                                id="image-preview"
                                src="<?= e($currentImageUrl) ?>"
                                alt="Image preview"
                                <?= $currentImageUrl === '' ? 'hidden' : '' ?>
                            >
                            <i id="image-placeholder" class="bi bi-cloud-arrow-up" <?= $currentImageUrl !== '' ? 'hidden' : '' ?>></i>
                        </span>
                        <span class="editor-image-copy">
                            <strong><?= $currentImageUrl === '' ? 'Choose or paste an image' : 'Choose a replacement image' ?></strong>
                            <small id="image-file-name">JPG, PNG, WEBP or GIF &bull; maximum 5MB</small>
                        </span>
                    </label>
                </div>
            </div>
        </section>

        <?php if ($currentKind === 'package'): ?>
            <section class="editor-panel">
                <div class="editor-panel-title">
                    <span><i class="bi bi-speedometer2"></i></span>
                    <div>
                        <h2>Plan allowances</h2>
                        <p>Use zero when an allowance does not apply.</p>
                    </div>
                </div>
                <div class="editor-fields editor-allowances">
                    <label class="editor-field">
                        <span>Data (GB)</span>
                        <input type="number" name="data_gb" min="0" step="1" required value="<?= e((string) ($item['data_gb'] ?? 0)) ?>">
                    </label>
                    <label class="editor-field">
                        <span>Minutes</span>
                        <input type="number" name="minutes" min="0" step="1" required value="<?= e((string) ($item['minutes'] ?? 0)) ?>">
                    </label>
                    <label class="editor-field">
                        <span>SMS</span>
                        <input type="number" name="sms" min="0" step="1" required value="<?= e((string) ($item['sms'] ?? 0)) ?>">
                    </label>
                </div>
            </section>
        <?php else: ?>
            <section class="editor-panel">
                <div class="editor-panel-title">
                    <span><i class="bi bi-boxes"></i></span>
                    <div>
                        <h2>Included packages</h2>
                        <p id="combo-selection-help">Select exactly 2 packages from 2 different categories.</p>
                    </div>
                </div>

                <div class="combo-package-picker">
                    <?php foreach (['Mobile', 'Broadband', 'Tablet'] as $category): ?>
                        <fieldset>
                            <legend><?= e($category) ?></legend>
                            <?php foreach ($availablePackages as $package): ?>
                                <?php if ($package['category'] !== $category) continue; ?>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="package_ids[]"
                                        value="<?= (int) $package['id'] ?>"
                                        data-package-price="<?= e((string) $package['price']) ?>"
                                        <?= in_array((int) $package['id'], $selectedPackageIds, true) ? 'checked' : '' ?>
                                    >
                                    <span>
                                        <strong><?= e($package['package_name']) ?></strong>
                                        <small>&pound;<?= number_format((float) $package['price'], 2) ?>/month</small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="editor-submit-bar">
            <?php if ($isEdit): ?>
                <button
                    class="btn package-delete-button editor-delete-button"
                    type="submit"
                    formaction="<?= url('/crm/package/delete') ?>"
                    formmethod="post"
                    data-confirm="Archive this <?= $currentKind === 'deal' ? 'combo' : 'package' ?>? It will be hidden from new orders while history is retained, and can be reactivated later."
                >
                    <i class="bi bi-archive"></i>
                    Archive <?= $currentKind === 'deal' ? 'combo' : 'package' ?>
                </button>
            <?php endif; ?>
            <a class="btn" href="<?= url('/crm/packages') ?>">Cancel</a>
            <button class="btn primary" type="submit">
                <i class="bi bi-check2-circle"></i>
                <?= $isEdit ? 'Save changes' : ($currentKind === 'deal' ? 'Add combo' : 'Add package') ?>
            </button>
        </div>
    </form>
</section>

<script>
(() => {
    const input = document.getElementById('image-file');
    const zone = document.getElementById('image-drop-zone');
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('image-placeholder');
    const fileName = document.getElementById('image-file-name');

    function showImage(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        placeholder.hidden = true;
        fileName.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)}MB`;
    }

    input.addEventListener('change', () => showImage(input.files[0]));
    zone.addEventListener('dragover', event => {
        event.preventDefault();
        zone.classList.add('is-dragging');
    });
    zone.addEventListener('dragleave', () => zone.classList.remove('is-dragging'));
    zone.addEventListener('drop', event => {
        event.preventDefault();
        zone.classList.remove('is-dragging');
        showImage(event.dataTransfer.files[0]);
    });
    document.addEventListener('paste', event => {
        const imageItem = [...event.clipboardData.items].find(item => item.type.startsWith('image/'));
        if (!imageItem) return;
        event.preventDefault();
        const extension = imageItem.type.split('/')[1] || 'png';
        const image = imageItem.getAsFile();
        showImage(new File([image], `pasted-image.${extension}`, {type: imageItem.type}));
    });
})();
</script>

<?php if ($currentKind === 'deal'): ?>
<script>
(() => {
    const type = document.getElementById('deal-type');
    const inputs = [...document.querySelectorAll('[name="package_ids[]"]')];
    const preview = document.getElementById('combo-price-preview');
    const help = document.getElementById('combo-selection-help');
    const form = document.querySelector('.package-editor-form');
    let selectionOrder = inputs.filter(input => input.checked);

    function expectedCount() {
        return type.value === 'TriplePackage' ? 3 : 2;
    }

    function normaliseSelection() {
        const selectedCategories = new Set();

        selectionOrder = selectionOrder.filter(input => input.checked);
        selectionOrder = selectionOrder.filter(input => {
            const category = input.closest('fieldset');
            if (selectedCategories.has(category)) {
                input.checked = false;
                return false;
            }
            selectedCategories.add(category);
            return true;
        });

        while (selectionOrder.length > expectedCount()) {
            selectionOrder.pop().checked = false;
        }
    }

    function refreshCombo() {
        normaliseSelection();

        const expected = expectedCount();
        const selected = inputs.filter(input => input.checked);
        const selectedCategories = new Set(selected.map(input => input.closest('fieldset')));
        const limitReached = selected.length >= expected;
        const original = selected.reduce((sum, input) => sum + Number(input.dataset.packagePrice), 0);
        const combo = original * .85;

        inputs.forEach(input => {
            const unavailable = !input.checked && (
                limitReached || selectedCategories.has(input.closest('fieldset'))
            );
            input.disabled = unavailable;
            input.closest('label').classList.toggle('is-disabled', unavailable);
        });

        help.textContent = `Select exactly ${expected} packages from ${expected} different categories.`;
        preview.textContent = selected.length
            ? `${selected.length}/${expected} selected · £${original.toFixed(2)} → £${combo.toFixed(2)}`
            : `Select ${expected} included packages`;
    }

    inputs.forEach(input => input.addEventListener('change', () => {
        selectionOrder = selectionOrder.filter(selected => selected !== input);
        if (input.checked) selectionOrder.push(input);
        refreshCombo();
    }));

    type.addEventListener('change', refreshCombo);

    form.addEventListener('submit', event => {
        if (event.submitter?.classList.contains('editor-delete-button')) return;
        if (inputs.filter(input => input.checked).length === expectedCount()) return;
        event.preventDefault();
        preview.scrollIntoView({behavior: 'smooth', block: 'center'});
        alert(`Please select exactly ${expectedCount()} packages from different categories.`);
    });

    refreshCombo();
})();
</script>
<?php endif; ?>

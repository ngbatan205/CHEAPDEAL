<section class="form-layout">
    <div>
        <p class="eyebrow">Customer support</p>
        <h1>Ask about a telecom deal</h1>
        <p>Send your question about a mobile, broadband, or tablet package. Logged-in enquiries are linked to your account.</p>
    </div>
    <form class="form-card" method="post" action="<?= url('/enquiry') ?>">
        <?= csrf_field() ?>
        <label>Package
            <select name="package_id">
                <?php foreach ($packages as $package): ?>
                    <option value="<?= (int) $package['id'] ?>"><?= e($package['package_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Subject<input required name="subject" value="<?= e($subject ?? '') ?>"></label>
        <label>Message<textarea required name="message" rows="5"></textarea></label>
        <button class="btn primary" type="submit">
            Send enquiry
        </button>
    </form>
</section>

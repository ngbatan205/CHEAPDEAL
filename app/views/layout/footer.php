</main>

<?php
$footerCustomer = $_SESSION['customer'] ?? null;
$footerIsStaff = in_array((string) ($footerCustomer['role'] ?? ''), ['admin', 'csr'], true);
$footerPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$footerIsOperationsContext = $footerIsStaff && str_contains($footerPath, '/crm');
?>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="brand-symbol" aria-hidden="true">CD</span>
            <div>
                <strong>CheapDeals</strong>
                <p><?= $footerIsOperationsContext ? 'Secure operations workspace for authorised staff.' : 'Clear telecom plans, secure checkout and helpful support.' ?></p>
            </div>
        </div>

        <?php if (!$footerIsOperationsContext): ?>
            <nav class="footer-links" aria-label="Footer navigation">
                <a href="<?= url('/packages') ?>">Plans</a>
                <a href="<?= url('/combos') ?>">Combos</a>
                <a href="<?= url('/offers') ?>">Offers</a>
                <a href="<?= url('/enquiry') ?>">Support</a>
            </nav>
        <?php endif; ?>

        <p class="footer-meta">&copy; <?= date('Y') ?> CheapDeals</p>
    </div>
</footer>

<div class="toast-container app-toast-container position-fixed end-0 p-3">
    <div id="app-toast" class="toast" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">CheapDeals</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close notification"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('/js/main.js') ?>?v=20260801.4"></script>

</body>
</html>

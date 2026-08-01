<?php
$navCustomer = $_SESSION['customer'] ?? null;
$staffRole = (string) ($navCustomer['role'] ?? '');
$isStaff = in_array($staffRole, ['admin', 'csr'], true);
$isCatalogueAdmin = $staffRole === 'admin';
$staffLabel = $staffRole === 'csr' ? 'Customer service' : 'Administrator';
$cartCount = array_sum(array_map('intval', $_SESSION['cart'] ?? []));

$relativePath = $currentPath ?? '/';
if (BASE_PATH !== '' && ($relativePath === BASE_PATH || str_starts_with($relativePath, BASE_PATH . '/'))) {
    $relativePath = substr($relativePath, strlen(BASE_PATH)) ?: '/';
}
$isOperationsContext = $isStaff
    && ($relativePath === '/crm' || str_starts_with($relativePath, '/crm/'));

$routeActive = static function (string ...$routes) use ($relativePath): bool {
    foreach ($routes as $route) {
        if ($route === '/') {
            if ($relativePath === '/') {
                return true;
            }
            continue;
        }
        if ($relativePath === $route || str_starts_with($relativePath, $route . '/')) {
            return true;
        }
    }
    return false;
};

$activeAttributes = static fn (bool $active): string => $active
    ? ' active" aria-current="page'
    : '';
?>

<nav
    class="navbar navbar-expand-xl sticky-top cd-navbar <?= $isOperationsContext ? 'operations-navbar' : 'store-navbar' ?>"
    aria-label="Primary navigation"
>
    <div class="container">
        <a
            class="navbar-brand"
            href="<?= url($isOperationsContext ? '/crm' : '/') ?>"
            aria-label="CheapDeals <?= $isOperationsContext ? 'operations home' : 'home' ?>"
        >
            <span class="brand-symbol" aria-hidden="true">CD</span>
            <span class="brand-copy">
                <strong>CheapDeals</strong>
                <?php if ($isOperationsContext): ?><small>Operations</small><?php endif; ?>
            </span>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-controls="mainNavbar"
            aria-expanded="false"
            aria-label="Open navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <?php if ($isOperationsContext): ?>
                <ul class="navbar-nav ms-auto align-items-xl-center operations-nav">
                    <li class="nav-item">
                        <a class="nav-link<?= $activeAttributes($relativePath === '/crm') ?>" href="<?= url('/crm') ?>">
                            <i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= $activeAttributes($routeActive('/crm/customers', '/crm/customer')) ?>" href="<?= url('/crm/customers') ?>">
                            <i class="bi bi-people" aria-hidden="true"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= $activeAttributes($routeActive('/crm/orders')) ?>" href="<?= url('/crm/orders') ?>">
                            <i class="bi bi-receipt" aria-hidden="true"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= $activeAttributes($routeActive('/crm/packages', '/crm/package')) ?>" href="<?= url('/crm/packages') ?>">
                            <i class="bi bi-box-seam" aria-hidden="true"></i> Catalogue
                        </a>
                    </li>
                    <?php if ($isCatalogueAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $activeAttributes($routeActive('/crm/offers', '/crm/offer')) ?>" href="<?= url('/crm/offers') ?>">
                                <i class="bi bi-ticket-perforated" aria-hidden="true"></i> Offers
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle<?= $activeAttributes($routeActive('/crm/enquiries', '/crm/telephone-order', '/crm/subscription-changes')) ?>"
                            href="<?= url('/crm/enquiries') ?>"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        ><i class="bi bi-headset" aria-hidden="true"></i> Customer service</a>
                        <ul class="dropdown-menu operations-service-menu">
                            <li><a class="dropdown-item" href="<?= url('/crm/enquiries') ?>"><i class="bi bi-chat-square-text" aria-hidden="true"></i> Enquiries</a></li>
                            <li><a class="dropdown-item" href="<?= url('/crm/telephone-order') ?>"><i class="bi bi-telephone-outbound" aria-hidden="true"></i> Telephone order</a></li>
                            <li><a class="dropdown-item" href="<?= url('/crm/subscription-changes') ?>"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Plan changes</a></li>
                        </ul>
                    </li>
                    <?php if ($isCatalogueAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $activeAttributes($routeActive('/crm/records', '/crm/record')) ?>" href="<?= url('/crm/records') ?>">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i> Records
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="navbar-utilities">
                    <a class="utility-link" href="<?= url('/packages') ?>">
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        <span>View store</span>
                    </a>
                    <div class="dropdown staff-account-menu">
                        <button class="account-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($navCustomer['full_name'] ?? 'S'), 0, 1))) ?></span>
                            <span><strong><?= e($navCustomer['full_name'] ?? 'Staff') ?></strong><small><?= e($staffLabel) ?></small></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-header"><?= e($navCustomer['email'] ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= url('/logout') ?>">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item text-danger" type="submit">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <ul class="navbar-nav ms-auto align-items-xl-center store-nav">
                    <li class="nav-item dropdown">
                        <a
                            class="nav-link dropdown-toggle<?= $activeAttributes($routeActive('/', '/packages', '/package')) ?>"
                            href="<?= url('/packages') ?>"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >Plans</a>
                        <ul class="dropdown-menu plans-dropdown">
                            <li><a class="dropdown-item" href="<?= url('/packages') ?>#packages">All plans</a></li>
                            <li><a class="dropdown-item" href="<?= url('/packages') ?>?category=Mobile#packages">Mobile</a></li>
                            <li><a class="dropdown-item" href="<?= url('/packages') ?>?category=Broadband#packages">Broadband</a></li>
                            <li><a class="dropdown-item" href="<?= url('/packages') ?>?category=Tablet#packages">Tablet</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link<?= $activeAttributes($routeActive('/combos', '/combo')) ?>" href="<?= url('/combos') ?>">Combos</a></li>
                    <li class="nav-item"><a class="nav-link<?= $activeAttributes($routeActive('/offers')) ?>" href="<?= url('/offers') ?>">Offers</a></li>
                    <li class="nav-item"><a class="nav-link<?= $activeAttributes($routeActive('/enquiry', '/messages')) ?>" href="<?= url('/enquiry') ?>">Support</a></li>
                </ul>

                <div class="navbar-utilities store-utilities">
                    <?php if (!$isStaff): ?>
                        <a
                            class="cart-nav-link<?= $activeAttributes($routeActive('/checkout', '/payment')) ?>"
                            href="<?= url('/checkout') ?>"
                            data-cart-bubble
                            aria-label="Open cart with <?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?>"
                        >
                            <i class="bi bi-bag" aria-hidden="true"></i>
                            <span class="cart-label">Cart</span>
                            <?php if ($cartCount > 0): ?><span class="cart-count"><?= $cartCount > 99 ? '99+' : $cartCount ?></span><?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($isStaff): ?>
                        <a class="staff-return-link" href="<?= url('/crm') ?>">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to operations
                        </a>
                        <div class="dropdown staff-account-menu">
                            <button class="account-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) ($navCustomer['full_name'] ?? 'S'), 0, 1))) ?></span>
                                <span><strong><?= e($navCustomer['full_name'] ?? 'Staff') ?></strong><small><?= e($staffLabel) ?></small></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-header"><?= e($navCustomer['email'] ?? '') ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="<?= url('/logout') ?>">
                                        <?= csrf_field() ?>
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php elseif ($navCustomer): ?>
                        <div class="dropdown customer-account-menu">
                            <button class="account-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="account-avatar" aria-hidden="true"><?= e(strtoupper(substr((string) $navCustomer['full_name'], 0, 1))) ?></span>
                                <span><strong><?= e($navCustomer['full_name']) ?></strong><small>My account</small></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('/account') ?>"><i class="bi bi-bag-check" aria-hidden="true"></i> Orders</a></li>
                                <li><a class="dropdown-item" href="<?= url('/subscription') ?>"><i class="bi bi-phone" aria-hidden="true"></i> Subscription</a></li>
                                <li><a class="dropdown-item" href="<?= url('/messages') ?>"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Messages</a></li>
                                <li><a class="dropdown-item" href="<?= url('/profile') ?>"><i class="bi bi-person" aria-hidden="true"></i> Profile and payment</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="<?= url('/logout') ?>">
                                        <?= csrf_field() ?>
                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Log out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="utility-link" href="<?= url('/register') ?>">Register</a>
                        <a class="nav-login" href="<?= url('/login') ?>"><i class="bi bi-person-circle" aria-hidden="true"></i> Log in</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (!empty($_SESSION['flash'])): ?>
    <?php
    $flashMessage = (string) $_SESSION['flash'];
    $flashIsError = preg_match(
        '/\b(incorrect|invalid|not found|could not|cannot|must|failed|expired|restricted|does not|already belongs|please enter)\b/i',
        $flashMessage
    ) === 1;
    $flashIsWarning = !$flashIsError && preg_match(
        '/\b(already active|please choose|choose an|verify this|please log in)\b/i',
        $flashMessage
    ) === 1;
    $flashClass = $flashIsError ? 'alert-danger' : ($flashIsWarning ? 'alert-warning' : 'alert-success');
    $flashIcon = $flashIsError ? 'bi-exclamation-circle-fill' : ($flashIsWarning ? 'bi-info-circle-fill' : 'bi-check-circle-fill');
    ?>
    <div class="flash-region" role="<?= $flashIsError ? 'alert' : 'status' ?>" aria-live="polite">
        <div class="alert <?= $flashClass ?>">
            <i class="bi <?= $flashIcon ?>" aria-hidden="true"></i>
            <span><?= e($flashMessage) ?></span>
        </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<main id="main-content">

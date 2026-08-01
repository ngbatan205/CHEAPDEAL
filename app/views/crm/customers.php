<section class="admin-hero">
    <div>
        <p class="eyebrow">Customer accounts</p>
        <h1>Find or create a customer</h1>
        <p>Search by partial name, email address or telephone number, then open a profile or create a validated account.</p>
    </div>
    <span class="workspace-role"><i class="bi bi-person-check" aria-hidden="true"></i> CSR workflow</span>
</section>

<section class="admin-section" aria-labelledby="customer-results-title">
    <form class="admin-filter-bar" method="get" action="<?= url('/crm/customers') ?>">
        <label for="customer-search">Search customers
            <input id="customer-search" name="q" value="<?= e($query ?? '') ?>" placeholder="Name, email or telephone" autocomplete="off">
        </label>
        <button class="btn primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
        <?php if (!empty($query)): ?><a class="btn" href="<?= url('/crm/customers') ?>">Clear search</a><?php endif; ?>
    </form>

    <div class="admin-subsection-heading">
        <h2 id="customer-results-title"><?= !empty($query) ? 'Search results' : 'Customer directory' ?></h2>
        <span class="status-pill is-muted"><?= count($customers) ?> customers</span>
    </div>
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">Customer account records</caption>
            <thead><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Telephone</th><th scope="col">Address</th><th scope="col">Role</th><th scope="col">Action</th></tr></thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>#<?= (int) $customer['id'] ?></td>
                        <td><strong><?= e($customer['full_name']) ?></strong></td>
                        <td><?= e($customer['email']) ?></td>
                        <td><?= e($customer['phone'] ?? 'Not provided') ?></td>
                        <td><?= e($customer['address'] ?? 'Not provided') ?></td>
                        <td><span class="status-pill is-muted"><?= e(ucfirst($customer['role'] ?? 'customer')) ?></span></td>
                        <td><a class="btn small" href="<?= url('/crm/customer?id=' . (int) $customer['id']) ?>">Open profile</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($customers)): ?><tr><td colspan="7">No customer matches this search.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="form-card spaced-card">
        <p class="eyebrow">New customer</p>
        <h2>Create a validated customer profile</h2>
        <p class="text-secondary">Use the caller's verified contact details. They can change their temporary password after logging in.</p>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i><span><?= e(implode(' ', array_values($errors))) ?></span></div>
        <?php endif; ?>
        <form method="post" action="<?= url('/crm/customers/create') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label for="new-customer-name">Full name<input id="new-customer-name" name="full_name" required autocomplete="name" value="<?= e($old['full_name'] ?? '') ?>"></label>
                <label for="new-customer-email">Email address<input id="new-customer-email" type="email" name="email" required autocomplete="email" value="<?= e($old['email'] ?? '') ?>"></label>
                <label for="new-customer-phone">Telephone number<input id="new-customer-phone" type="tel" name="phone" required autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"></label>
                <label for="new-customer-address">Address<input id="new-customer-address" name="address" required autocomplete="street-address" value="<?= e($old['address'] ?? '') ?>"></label>
                <label for="new-customer-password">Temporary password<input id="new-customer-password" type="password" name="password" minlength="8" required autocomplete="new-password"></label>
            </div>
            <button class="btn primary" type="submit"><i class="bi bi-person-plus" aria-hidden="true"></i> Create customer</button>
        </form>
    </div>
</section>

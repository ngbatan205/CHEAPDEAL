<?php

class Database
{
    private PDO $pdo;

    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->ensureSchema();
        $this->seedIfEmpty();
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function executeAffected(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    private function ensureSchema(): void
    {
        $paymentColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM payments'),
            'Field'
        );

        if (!in_array('verification_status', $paymentColumns, true)) {
            $this->execute(
                'ALTER TABLE payments
                 ADD verification_status VARCHAR(20) NULL AFTER payment_status'
            );
        }
        if (!in_array('verification_reference', $paymentColumns, true)) {
            $this->execute(
                'ALTER TABLE payments
                 ADD verification_reference VARCHAR(60) NULL AFTER verification_status'
            );
        }
        if (!in_array('verification_message', $paymentColumns, true)) {
            $this->execute(
                'ALTER TABLE payments
                 ADD verification_message VARCHAR(255) NULL AFTER verification_reference'
            );
        }

        $paymentMethodColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM payment_methods'),
            'Field'
        );
        if (!in_array('verification_status', $paymentMethodColumns, true)) {
            $this->execute(
                'ALTER TABLE payment_methods
                 ADD verification_status VARCHAR(20) NULL AFTER card_expiry'
            );
        }
        if (!in_array('verification_reference', $paymentMethodColumns, true)) {
            $this->execute(
                'ALTER TABLE payment_methods
                 ADD verification_reference VARCHAR(60) NULL AFTER verification_status'
            );
        }

        $orderColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM orders'),
            'Field'
        );

        if (!in_array('quantity', $orderColumns, true)) {
            $this->execute(
                'ALTER TABLE orders
                 ADD quantity INT NOT NULL DEFAULT 1 AFTER package_id'
            );
        }
        if (!in_array('order_channel', $orderColumns, true)) {
            $this->execute(
                "ALTER TABLE orders
                 ADD order_channel VARCHAR(20) NOT NULL DEFAULT 'Website' AFTER status"
            );
        }
        if (!in_array('created_by', $orderColumns, true)) {
            $this->execute(
                'ALTER TABLE orders
                 ADD created_by INT NULL AFTER order_channel'
            );
        }

        $packageColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM packages'),
            'Field'
        );
        if (!in_array('is_active', $packageColumns, true)) {
            $this->execute(
                'ALTER TABLE packages ADD is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER image'
            );
        }

        $dealColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM deals'),
            'Field'
        );
        if (!in_array('is_active', $dealColumns, true)) {
            $this->execute(
                'ALTER TABLE deals ADD is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER image'
            );
        }

        $offerColumns = array_column(
            $this->fetchAll('SHOW COLUMNS FROM offers'),
            'Field'
        );
        if (!in_array('is_active', $offerColumns, true)) {
            $this->execute(
                'ALTER TABLE offers ADD is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER expiry_date'
            );
        }
        if (!$this->fetch("SHOW INDEX FROM offers WHERE Key_name = 'uq_offers_code'")) {
            $this->execute('ALTER TABLE offers ADD UNIQUE KEY uq_offers_code (code)');
        }
        if (!$this->fetch("SHOW INDEX FROM offers WHERE Key_name = 'idx_offers_visibility'")) {
            $this->execute(
                'ALTER TABLE offers ADD KEY idx_offers_visibility (is_active, expiry_date)'
            );
        }

        $this->execute(
            'CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                package_id INT NULL,
                deal_id INT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'Active\',
                started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                renewal_date DATE NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_subscription_user (user_id),
                CONSTRAINT fk_subscription_user FOREIGN KEY (user_id) REFERENCES users(id),
                CONSTRAINT fk_subscription_package FOREIGN KEY (package_id) REFERENCES packages(id),
                CONSTRAINT fk_subscription_deal FOREIGN KEY (deal_id) REFERENCES deals(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $subscriptionColumnRows = $this->fetchAll('SHOW COLUMNS FROM subscriptions');
        $subscriptionColumns = array_column($subscriptionColumnRows, 'Field');
        if (!in_array('deal_id', $subscriptionColumns, true)) {
            $this->execute('ALTER TABLE subscriptions ADD deal_id INT NULL AFTER package_id');
        }
        // Earlier prototype builds required package_id. It must become nullable
        // when the current subscription is a DoublePackage/TriplePackage deal.
        $packageColumn = array_values(array_filter(
            $subscriptionColumnRows,
            static fn (array $column): bool => $column['Field'] === 'package_id'
        ))[0] ?? null;
        if ($packageColumn && strtoupper((string) ($packageColumn['Null'] ?? 'NO')) !== 'YES') {
            $this->execute('ALTER TABLE subscriptions MODIFY package_id INT NULL');
        }

        $this->execute(
            'CREATE TABLE IF NOT EXISTS audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                actor_id INT NULL,
                event_type VARCHAR(80) NOT NULL,
                entity_type VARCHAR(40) NOT NULL,
                entity_id INT NULL,
                outcome VARCHAR(30) NOT NULL DEFAULT \'Success\',
                details VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_audit_created (created_at),
                INDEX idx_audit_entity (entity_type, entity_id),
                CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        // The original prototype column is retained only for backward schema
        // compatibility. Full card values are deliberately scrubbed.
        $this->execute('UPDATE users SET credit_card = NULL WHERE credit_card IS NOT NULL');
    }

    private function seedIfEmpty(): void
    {
        $packageCount = (int) ($this->fetch('SELECT COUNT(*) AS total FROM packages')['total'] ?? 0);
        if ($packageCount === 0) {
            $packages = [
                ['Starter Mobile', 'Mobile', 12.99, 250, 500, 8, 'Budget mobile plan with enough data and calls for everyday use.', 100, 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80'],
                ['Unlimited Mobile Max', 'Mobile', 29.99, 5000, 5000, 80, 'High data mobile package for streaming, social media, and work on the go.', 80, 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=1200&q=80'],
                ['Home Broadband 100', 'Broadband', 24.99, 0, 0, 500, 'Reliable broadband package for households, online classes, and remote work.', 60, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80'],
                ['Tablet Data Share', 'Tablet', 15.99, 0, 100, 25, 'Flexible tablet data package for entertainment, browsing, and travel.', 75, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=1200&q=80'],
            ];

            foreach ($packages as $package) {
                $this->execute(
                    'INSERT INTO packages (package_name, category, price, minutes, sms, data_gb, description, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    $package
                );
            }
        }

        $offerCount = (int) ($this->fetch('SELECT COUNT(*) AS total FROM offers')['total'] ?? 0);
        if ($offerCount === 0) {
            $this->execute(
                'INSERT INTO offers (code, description, discount_percent, expiry_date)
                 VALUES (?, ?, ?, NULL)',
                ['PLAN10', '10% off any single-category package.', 10]
            );
        }

        $userCount = (int) ($this->fetch('SELECT COUNT(*) AS total FROM users')['total'] ?? 0);
        if ($userCount === 0) {
            $this->execute('INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)', ['Demo Customer', 'customer@example.com', password_hash('password', PASSWORD_DEFAULT), '+84 900 000 000', 'Hanoi, Vietnam', 'customer']);
            $this->execute('INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)', ['Admin User', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), '+84 911 111 111', 'Ho Chi Minh City, Vietnam', 'admin']);
        }

        // Existing coursework databases can already contain customers/admins
        // but still miss the CSR role required by telephone ordering.
        if (!$this->fetch('SELECT id FROM users WHERE email = ?', ['csr@example.com'])) {
            $this->execute(
                'INSERT INTO users (full_name, email, password, phone, address, role)
                 VALUES (?, ?, ?, ?, ?, ?)',
                ['CSR User', 'csr@example.com', password_hash('password', PASSWORD_DEFAULT), '+84 922 222 222', 'Da Nang, Vietnam', 'csr']
            );
        }
    }
}

<?php

use Ogan\Database\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    protected string $table = 'users';

    public function up(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = match ($driver) {
            'mysql' => "
                CREATE TABLE users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    roles JSON DEFAULT NULL,
                    email_verified_at DATETIME DEFAULT NULL,
                    email_verification_token VARCHAR(255) DEFAULT NULL,
                    password_reset_token VARCHAR(255) DEFAULT NULL,
                    password_reset_expires_at DATETIME DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_email (email),
                    INDEX idx_verification_token (email_verification_token),
                    INDEX idx_reset_token (password_reset_token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'pgsql' => "
                CREATE TABLE users (
                    id SERIAL PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    roles JSONB DEFAULT '[]',
                    email_verified_at TIMESTAMP DEFAULT NULL,
                    email_verification_token VARCHAR(255) DEFAULT NULL,
                    password_reset_token VARCHAR(255) DEFAULT NULL,
                    password_reset_expires_at TIMESTAMP DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                CREATE INDEX idx_users_email ON users(email);
                CREATE INDEX idx_users_verification_token ON users(email_verification_token);
                CREATE INDEX idx_users_reset_token ON users(password_reset_token);
            ",
            'sqlite' => "
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    roles TEXT DEFAULT '[]',
                    email_verified_at DATETIME DEFAULT NULL,
                    email_verification_token VARCHAR(255) DEFAULT NULL,
                    password_reset_token VARCHAR(255) DEFAULT NULL,
                    password_reset_expires_at DATETIME DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE INDEX idx_users_email ON users(email);
                CREATE INDEX idx_users_verification_token ON users(email_verification_token);
                CREATE INDEX idx_users_reset_token ON users(password_reset_token);
            ",
            default => throw new \RuntimeException("Driver non supporté: {$driver}")
        };

        $this->pdo->exec($sql);
    }

    public function down(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
    }
}
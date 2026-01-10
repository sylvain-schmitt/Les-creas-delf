<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Création de la table articles
 * ═══════════════════════════════════════════════════════════════════════
 * 
 * Cette migration a été générée automatiquement depuis le modèle Article.
 * 
 * Table : articles
 * Modèle : App\Model\Article
 * 
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class CreateArticleTable extends AbstractMigration
{
    /**
     * ═══════════════════════════════════════════════════════════════════
     * APPLIQUER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function up(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = match (strtolower($driver)) {
            'mysql', 'mariadb' => "
                CREATE TABLE IF NOT EXISTS articles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255),
                    slug VARCHAR(255) UNIQUE,
                    excerpt VARCHAR(255),
                    content TEXT,
                    featured_image_id INT,
                    category_id INT,
                    user_id INT,
                    status VARCHAR(255) NOT NULL,
                    published_at VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_featured_image_id (featured_image_id),
                    INDEX idx_category_id (category_id),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'pgsql', 'postgresql' => "
                CREATE TABLE IF NOT EXISTS articles (
                    id SERIAL PRIMARY KEY,
                    title VARCHAR(255),
                    slug VARCHAR(255) UNIQUE,
                    excerpt VARCHAR(255),
                    content TEXT,
                    featured_image_id INT,
                    category_id INT,
                    user_id INT,
                    status VARCHAR(255) NOT NULL,
                    published_at VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE INDEX IF NOT EXISTS idx_featured_image_id ON articles(featured_image_id);
                CREATE INDEX IF NOT EXISTS idx_category_id ON articles(category_id);
                CREATE INDEX IF NOT EXISTS idx_user_id ON articles(user_id);
            ",
            'sqlite' => "
                CREATE TABLE IF NOT EXISTS articles (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(255),
                    slug VARCHAR(255) UNIQUE,
                    excerpt VARCHAR(255),
                    content TEXT,
                    featured_image_id INTEGER,
                    category_id INTEGER,
                    user_id INTEGER,
                    status VARCHAR(255) NOT NULL,
                    published_at VARCHAR(255),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE INDEX IF NOT EXISTS idx_featured_image_id ON articles(featured_image_id);
                CREATE INDEX IF NOT EXISTS idx_category_id ON articles(category_id);
                CREATE INDEX IF NOT EXISTS idx_user_id ON articles(user_id);
            ",
            default => throw new \RuntimeException("Driver de base de données non supporté: {$driver}")
        };

        $this->execute($sql);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ANNULER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS articles");
    }
}

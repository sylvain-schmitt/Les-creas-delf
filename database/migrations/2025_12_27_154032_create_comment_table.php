<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Création de la table comments
 * ═══════════════════════════════════════════════════════════════════════
 * 
 * Cette migration a été générée automatiquement depuis le modèle Comment.
 * 
 * Table : comments
 * Modèle : App\Model\Comment
 * 
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class CreateCommentTable extends AbstractMigration
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
                CREATE TABLE IF NOT EXISTS comments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    article_id INT,
                    user_id INT,
                    author_name VARCHAR(255),
                    author_email VARCHAR(255),
                    content TEXT,
                    status VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_article_id (article_id),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'pgsql', 'postgresql' => "
                CREATE TABLE IF NOT EXISTS comments (
                    id SERIAL PRIMARY KEY,
                    article_id INT,
                    user_id INT,
                    author_name VARCHAR(255),
                    author_email VARCHAR(255),
                    content TEXT,
                    status VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );

                CREATE INDEX IF NOT EXISTS idx_article_id ON comments(article_id);
                CREATE INDEX IF NOT EXISTS idx_user_id ON comments(user_id);
            ",
            'sqlite' => "
                CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    article_id INTEGER,
                    user_id INTEGER,
                    author_name VARCHAR(255),
                    author_email VARCHAR(255),
                    content TEXT,
                    status VARCHAR(255) NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );

                CREATE INDEX IF NOT EXISTS idx_article_id ON comments(article_id);
                CREATE INDEX IF NOT EXISTS idx_user_id ON comments(user_id);
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
        $this->execute("DROP TABLE IF EXISTS comments");
    }
}

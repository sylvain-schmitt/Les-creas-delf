<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Création de la table pivot article_tag
 * ═══════════════════════════════════════════════════════════════════════
 * 
 * Table pivot pour la relation many-to-many entre articles et tags.
 * 
 * Table : article_tag
 * 
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class CreateArticleTagTable extends AbstractMigration
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
                CREATE TABLE IF NOT EXISTS article_tag (
                    article_id INT NOT NULL,
                    tag_id INT NOT NULL,
                    PRIMARY KEY (article_id, tag_id),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'pgsql', 'postgresql' => "
                CREATE TABLE IF NOT EXISTS article_tag (
                    article_id INTEGER NOT NULL,
                    tag_id INTEGER NOT NULL,
                    PRIMARY KEY (article_id, tag_id),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
                );
            ",
            'sqlite' => "
                CREATE TABLE IF NOT EXISTS article_tag (
                    article_id INTEGER NOT NULL,
                    tag_id INTEGER NOT NULL,
                    PRIMARY KEY (article_id, tag_id),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
                );
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
        $this->execute("DROP TABLE IF EXISTS article_tag");
    }
}

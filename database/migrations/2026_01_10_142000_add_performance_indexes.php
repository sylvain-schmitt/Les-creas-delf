<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Ajout d'index de performance
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Cette migration ajoute des index pour améliorer les performances
 * des requêtes les plus fréquentes (filtrage par status, tri par date).
 *
 * Index ajoutés :
 * - articles.status (filtrage des articles publiés/brouillons)
 * - articles.published_at (tri par date de publication)
 * - comments.status (filtrage des commentaires approuvés/en attente)
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class AddPerformanceIndexes extends AbstractMigration
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
                CREATE INDEX idx_articles_status ON articles(status);
                CREATE INDEX idx_articles_published_at ON articles(published_at);
                CREATE INDEX idx_comments_status ON comments(status);
            ",
            'pgsql', 'postgresql' => "
                CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status);
                CREATE INDEX IF NOT EXISTS idx_articles_published_at ON articles(published_at);
                CREATE INDEX IF NOT EXISTS idx_comments_status ON comments(status);
            ",
            'sqlite' => "
                CREATE INDEX IF NOT EXISTS idx_articles_status ON articles(status);
                CREATE INDEX IF NOT EXISTS idx_articles_published_at ON articles(published_at);
                CREATE INDEX IF NOT EXISTS idx_comments_status ON comments(status);
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
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = match (strtolower($driver)) {
            'mysql', 'mariadb' => "
                DROP INDEX idx_articles_status ON articles;
                DROP INDEX idx_articles_published_at ON articles;
                DROP INDEX idx_comments_status ON comments;
            ",
            'pgsql', 'postgresql' => "
                DROP INDEX IF EXISTS idx_articles_status;
                DROP INDEX IF EXISTS idx_articles_published_at;
                DROP INDEX IF EXISTS idx_comments_status;
            ",
            'sqlite' => "
                DROP INDEX IF EXISTS idx_articles_status;
                DROP INDEX IF EXISTS idx_articles_published_at;
                DROP INDEX IF EXISTS idx_comments_status;
            ",
            default => throw new \RuntimeException("Driver de base de données non supporté: {$driver}")
        };

        $this->execute($sql);
    }
}

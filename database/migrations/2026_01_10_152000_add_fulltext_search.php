<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Ajout de la recherche full-text PostgreSQL
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Cette migration ajoute le support de la recherche full-text native
 * PostgreSQL sur la table articles.
 *
 * Fonctionnalités :
 * - Colonne tsvector pour les données indexées
 * - Index GIN pour des recherches rapides
 * - Trigger automatique pour mettre à jour le vecteur
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class AddFullTextSearch extends AbstractMigration
{
    /**
     * ═══════════════════════════════════════════════════════════════════
     * APPLIQUER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function up(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (strtolower($driver) !== 'pgsql') {
            // Pour les autres bases de données, on utilisera LIKE
            // Aucune modification nécessaire
            return;
        }

        // PostgreSQL : ajouter colonne tsvector + index GIN + trigger
        $this->execute("
            -- Ajouter la colonne de recherche full-text
            ALTER TABLE articles
            ADD COLUMN IF NOT EXISTS search_vector tsvector;

            -- Créer l'index GIN pour des recherches rapides
            CREATE INDEX IF NOT EXISTS idx_articles_search
            ON articles USING GIN(search_vector);

            -- Créer la fonction de mise à jour du vecteur
            CREATE OR REPLACE FUNCTION articles_search_update()
            RETURNS trigger AS $$
            BEGIN
                NEW.search_vector :=
                    setweight(to_tsvector('french', COALESCE(NEW.title, '')), 'A') ||
                    setweight(to_tsvector('french', COALESCE(NEW.excerpt, '')), 'B') ||
                    setweight(to_tsvector('french', COALESCE(NEW.content, '')), 'C');
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            -- Créer le trigger
            DROP TRIGGER IF EXISTS articles_search_trigger ON articles;
            CREATE TRIGGER articles_search_trigger
            BEFORE INSERT OR UPDATE ON articles
            FOR EACH ROW EXECUTE FUNCTION articles_search_update();

            -- Mettre à jour les articles existants
            UPDATE articles SET search_vector =
                setweight(to_tsvector('french', COALESCE(title, '')), 'A') ||
                setweight(to_tsvector('french', COALESCE(excerpt, '')), 'B') ||
                setweight(to_tsvector('french', COALESCE(content, '')), 'C');
        ");
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ANNULER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function down(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (strtolower($driver) !== 'pgsql') {
            return;
        }

        $this->execute("
            DROP TRIGGER IF EXISTS articles_search_trigger ON articles;
            DROP FUNCTION IF EXISTS articles_search_update();
            DROP INDEX IF EXISTS idx_articles_search;
            ALTER TABLE articles DROP COLUMN IF EXISTS search_vector;
        ");
    }
}

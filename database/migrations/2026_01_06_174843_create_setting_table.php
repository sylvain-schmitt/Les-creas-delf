<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Création de la table settings
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Cette migration a été générée automatiquement depuis le modèle Setting.
 *
 * Table : settings
 * Modèle : App\Model\Setting
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class CreateSettingTable extends AbstractMigration
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
                CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(255) NOT NULL UNIQUE,
                    value TEXT,
                    type VARCHAR(50) NOT NULL DEFAULT 'text',
                    group_name VARCHAR(100) NOT NULL DEFAULT 'general',
                    label VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_group (group_name),
                    INDEX idx_key (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'pgsql', 'postgresql' => "
                CREATE TABLE IF NOT EXISTS settings (
                    id SERIAL PRIMARY KEY,
                    key VARCHAR(255) NOT NULL UNIQUE,
                    value TEXT,
                    type VARCHAR(50) NOT NULL DEFAULT 'text',
                    group_name VARCHAR(100) NOT NULL DEFAULT 'general',
                    label VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ",
            'sqlite' => "
                CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    key VARCHAR(255) NOT NULL UNIQUE,
                    value TEXT,
                    type VARCHAR(50) NOT NULL DEFAULT 'text',
                    group_name VARCHAR(100) NOT NULL DEFAULT 'general',
                    label VARCHAR(255),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ",
            default => throw new \RuntimeException("Driver de base de données non supporté: {$driver}")
        };

        $this->execute($sql);

        // Créer les index pour PostgreSQL
        if (in_array(strtolower($driver), ['pgsql', 'postgresql'])) {
            $this->execute("CREATE INDEX IF NOT EXISTS idx_settings_group ON settings(group_name)");
            $this->execute("CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(key)");
        }

        // Insérer les settings par défaut
        $this->insertDefaultSettings();
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ANNULER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS settings");
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * INSÉRER LES SETTINGS PAR DÉFAUT
     * ═══════════════════════════════════════════════════════════════════
     */
    private function insertDefaultSettings(): void
    {
        $settings = [
            // Groupe About
            ['about_image', null, 'image', 'about', 'Photo de la page À propos'],
            ['about_title', 'À propos', 'text', 'about', 'Titre hero'],
            ['about_subtitle', "Découvrez l'histoire derrière Les Créas d'Elf", 'text', 'about', 'Sous-titre hero'],
            ['about_story', null, 'textarea', 'about', 'Texte "Mon histoire"'],

            // Groupe Blog
            ['blog_title', 'Le Blog', 'text', 'blog', 'Titre hero'],
            ['blog_subtitle', 'Mes articles et inspirations créatives', 'text', 'blog', 'Sous-titre hero'],

            // Groupe Contact
            ['contact_title', 'Contact', 'text', 'contact', 'Titre hero'],
            ['contact_subtitle', "Une question ? N'hésitez pas à me contacter !", 'text', 'contact', 'Sous-titre hero'],

            // Groupe Social
            ['social_instagram', null, 'url', 'social', 'Lien Instagram'],
            ['social_facebook', null, 'url', 'social', 'Lien Facebook'],
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO settings (key, value, type, group_name, label, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");

        foreach ($settings as $s) {
            $stmt->execute($s);
        }
    }
}

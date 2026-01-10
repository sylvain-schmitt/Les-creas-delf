<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * MIGRATION : Changer le type de about_story en wysiwyg
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Cette migration change le type du setting about_story de textarea à wysiwyg
 * pour permettre l'utilisation de l'éditeur TinyMCE.
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Database\Migration;

use Ogan\Database\Migration\AbstractMigration;

class ChangeAboutStoryToWysiwyg extends AbstractMigration
{
    /**
     * ═══════════════════════════════════════════════════════════════════
     * APPLIQUER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function up(): void
    {
        $this->execute("UPDATE settings SET type = 'wysiwyg' WHERE key = 'about_story'");
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * ANNULER LA MIGRATION
     * ═══════════════════════════════════════════════════════════════════
     */
    public function down(): void
    {
        $this->execute("UPDATE settings SET type = 'textarea' WHERE key = 'about_story'");
    }
}

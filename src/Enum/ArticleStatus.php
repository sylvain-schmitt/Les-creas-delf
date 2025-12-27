<?php

namespace App\Enum;

/**
 * Statut d'un article
 */
enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Label pour l'affichage
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
            self::ARCHIVED => 'Archivé',
        };
    }

    /**
     * Couleur pour le badge
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'yellow',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'gray',
        };
    }

    /**
     * Classe CSS pour le badge
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-yellow-100 text-yellow-800',
            self::PUBLISHED => 'bg-green-100 text-green-800',
            self::ARCHIVED => 'bg-gray-100 text-gray-800',
        };
    }
}

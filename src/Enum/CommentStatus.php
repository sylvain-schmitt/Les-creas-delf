<?php

namespace App\Enum;

/**
 * Statut d'un commentaire
 */
enum CommentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    /**
     * Label pour l'affichage
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
        };
    }

    /**
     * Couleur pour le badge
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
        };
    }

    /**
     * Classe CSS pour le badge
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
        };
    }
}

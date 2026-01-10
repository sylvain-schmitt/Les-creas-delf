<?php

namespace App\Repository;

use App\Model\Setting;
use App\Model\Media;
use Ogan\Database\Database;

class SettingRepository
{
    /**
     * Cache des settings pour éviter les requêtes répétées
     */
    private static array $cache = [];

    /**
     * Récupérer une valeur de setting
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $setting = $this->findByKey($key);

        if ($setting === null) {
            return $default;
        }

        $value = $setting->getValue();

        // Pour les images, retourner l'URL du média
        if ($setting->getType() === 'image' && $value) {
            $media = Media::find((int) $value);
            if ($media) {
                $value = $media->getUrl();
            }
        }

        self::$cache[$key] = $value;
        return $value ?? $default;
    }

    /**
     * Définir une valeur de setting
     */
    public function set(string $key, mixed $value): bool
    {
        $setting = $this->findByKey($key);

        if ($setting === null) {
            return false;
        }

        $setting->setValue($value);
        $setting->setUpdatedAt(new \DateTime());
        $result = $setting->save();

        unset(self::$cache[$key]);
        return $result;
    }

    /**
     * Trouver un setting par sa clé
     */
    public function findByKey(string $key): ?Setting
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $setting = new Setting($row);
        $setting->markAsExisting();
        return $setting;
    }

    /**
     * Récupérer tous les settings d'un groupe
     *
     * @return Setting[]
     */
    public function findByGroup(string $group): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM settings WHERE group_name = ? ORDER BY id ASC");
        $stmt->execute([$group]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            $setting = new Setting($row);
            $setting->markAsExisting();
            return $setting;
        }, $results);
    }

    /**
     * Récupérer tous les groupes disponibles
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT group_name FROM settings ORDER BY group_name");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Compter les settings d'un groupe
     */
    public function countByGroup(string $group): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE group_name = ?");
        $stmt->execute([$group]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Vider le cache
     */
    public function clearCache(): void
    {
        self::$cache = [];
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS D'AFFICHAGE
    // ─────────────────────────────────────────────────────────────

    /**
     * Label du groupe pour l'affichage
     */
    public function getGroupLabel(string $group): string
    {
        return match ($group) {
            'about' => 'Page À propos',
            'blog' => 'Page Blog',
            'contact' => 'Page Contact',
            'social' => 'Réseaux sociaux',
            'general' => 'Général',
            default => ucfirst($group)
        };
    }

    /**
     * Icône du groupe
     */
    public function getGroupIcon(string $group): string
    {
        return match ($group) {
            'about' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'blog' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
            'contact' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
            'social' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            default => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
        };
    }
}

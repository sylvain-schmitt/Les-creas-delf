<?php

/**
 * ═══════════════════════════════════════════════════════════════
 * 🔧 HELPER FUNCTIONS
 * ═══════════════════════════════════════════════════════════════
 *
 * Fonctions globales disponibles dans toute l'application
 * Chargées automatiquement via composer autoload
 */

use App\Repository\SettingRepository;
use App\Model\Media;

if (!function_exists('setting')) {
    /**
     * Récupère une valeur de setting depuis la base de données
     *
     * Usage dans les templates:
     *   {{ setting('about_title') }}
     *   {{ setting('about_image', 'default.jpg') }}
     *
     * Pour les images, retourne l'URL complète du média
     *
     * @param string $key La clé du setting
     * @param mixed $default Valeur par défaut si le setting n'existe pas
     * @return mixed La valeur du setting ou la valeur par défaut
     */
    function setting(string $key, mixed $default = null): mixed
    {
        static $repository = null;

        if ($repository === null) {
            $repository = new SettingRepository();
        }

        return $repository->get($key, $default);
    }
}

if (!function_exists('setting_image')) {
    /**
     * Récupère l'URL d'une image de setting
     *
     * Usage dans les templates:
     *   {{ setting_image('about_image') }}
     *
     * @param string $key La clé du setting de type image
     * @param string|null $default URL par défaut si l'image n'existe pas
     * @return string|null L'URL de l'image ou la valeur par défaut
     */
    function setting_image(string $key, ?string $default = null): ?string
    {
        static $repository = null;

        if ($repository === null) {
            $repository = new SettingRepository();
        }

        $setting = $repository->findByKey($key);

        if ($setting === null || $setting->getValue() === null) {
            return $default;
        }

        // Si c'est une image, récupérer le média
        if ($setting->getType() === 'image') {
            $media = Media::find((int) $setting->getValue());
            if ($media) {
                return $media->getUrl();
            }
            return $default;
        }

        return $setting->getValue() ?: $default;
    }
}

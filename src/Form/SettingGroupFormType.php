<?php

/**
 * ═══════════════════════════════════════════════════════════════════════
 * SETTING GROUP FORM TYPE
 * ═══════════════════════════════════════════════════════════════════════
 *
 * FormType dynamique pour les settings d'un groupe.
 * Génère automatiquement les champs en fonction du type de chaque setting.
 *
 * Usage:
 *   $form = $this->createFormBuilder(SettingGroupFormType::class, [
 *       'settings' => $settings,
 *       'settingsWithMedia' => $settingsWithMedia
 *   ]);
 *
 * ═══════════════════════════════════════════════════════════════════════
 */

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\TextareaType;
use Ogan\Form\Types\WysiwygType;
use Ogan\Form\Types\UrlType;
use Ogan\Form\Types\SubmitType;

class SettingGroupFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $settingsWithMedia = $options['settingsWithMedia'] ?? [];

        foreach ($settingsWithMedia as $item) {
            $setting = $item['setting'];
            $key = $setting->getKey();
            $label = $setting->getLabel();
            $type = $setting->getType();
            $value = $setting->getValue();

            // Skip les images - elles seront gérées séparément avec le media picker
            if ($type === 'image') {
                continue;
            }

            // Déterminer le type de champ en fonction du type de setting
            switch ($type) {
                case 'wysiwyg':
                    $builder->add($key, WysiwygType::class, [
                        'label' => $label,
                        'required' => false,
                        'toolbar' => 'simple',
                        'height' => 300,
                        'attr' => [
                            'rows' => 10
                        ]
                    ]);
                    break;

                case 'textarea':
                    $builder->add($key, TextareaType::class, [
                        'label' => $label,
                        'required' => false,
                        'attr' => [
                            'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all resize-none',
                            'rows' => 4
                        ]
                    ]);
                    break;

                case 'url':
                    $builder->add($key, TextType::class, [
                        'label' => $label,
                        'required' => false,
                        'attr' => [
                            'type' => 'url',
                            'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                            'placeholder' => 'https://...'
                        ]
                    ]);
                    break;

                default: // text
                    $builder->add($key, TextType::class, [
                        'label' => $label,
                        'required' => false,
                        'attr' => [
                            'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all'
                        ]
                    ]);
                    break;
            }
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Enregistrer',
            'attr' => ['class' => 'btn-primary px-6 py-3 flex items-center gap-2']
        ]);
    }
}

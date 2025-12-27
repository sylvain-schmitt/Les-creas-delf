<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\TextareaType;
use Ogan\Form\Types\SelectType;
use Ogan\Form\Types\WysiwygType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\MinLength;
use App\Enum\ArticleStatus;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        // Build category choices
        $categoryChoices = ['' => 'Sélectionner une catégorie'];
        if (!empty($options['categories'])) {
            foreach ($options['categories'] as $category) {
                $categoryChoices[$category->id] = $category->name;
            }
        }

        // Build status choices
        $statusChoices = [
            ArticleStatus::DRAFT->value => ArticleStatus::DRAFT->label(),
            ArticleStatus::PUBLISHED->value => ArticleStatus::PUBLISHED->label(),
            ArticleStatus::ARCHIVED->value => ArticleStatus::ARCHIVED->label()
        ];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'constraints' => [
                    new Required('Le titre est obligatoire'),
                    new MinLength(5, 'Le titre doit contenir au moins 5 caractères')
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Titre de l\'article'
                ]
            ])
            ->add('content', WysiwygType::class, [
                'label' => 'Contenu',
                'required' => false,
                'toolbar' => 'full',
                'height' => 400,
                'attr' => [
                    'rows' => 15
                ]
            ])
            ->add('category_id', SelectType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'choices' => $categoryChoices,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all'
                ]
            ])
            ->add('status', SelectType::class, [
                'label' => 'Statut',
                'choices' => $statusChoices,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

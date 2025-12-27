<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\TextareaType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\MinLength;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'constraints' => [
                    new Required('Le nom est obligatoire'),
                    new MinLength(2, 'Le nom doit contenir au moins 2 caractères')
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Ex: Tutoriels'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Description optionnelle de la catégorie',
                    'rows' => 3
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

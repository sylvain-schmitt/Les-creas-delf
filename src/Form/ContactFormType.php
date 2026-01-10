<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\EmailType;
use Ogan\Form\Types\TextareaType;
use Ogan\Form\Types\SelectType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\Email;
use Ogan\Form\Constraint\MinLength;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Jean Dupont',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('Le nom est obligatoire'),
                    new MinLength(2, 'Le nom doit faire au moins 2 caractères')
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'jean@exemple.com',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('L\'email est obligatoire'),
                    new Email('L\'email n\'est pas valide')
                ]
            ])
            ->add('subject', SelectType::class, [
                'label' => 'Sujet',
                'choices' => [
                    '' => 'Choisir un sujet...',
                    'general' => 'Question générale',
                    'commande' => 'Commande personnalisée',
                    'partenariat' => 'Partenariat',
                    'presse' => 'Presse',
                    'autre' => 'Autre demande'
                ],
                'attr' => [
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('Veuillez choisir un sujet')
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Bonjour, je vous contacte car...',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('Le message est obligatoire'),
                    new MinLength(10, 'Le message doit faire au moins 10 caractères')
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer le message',
                'attr' => [
                    'class' => 'inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-8 py-4 font-semibold text-white shadow-lg transition-all hover:bg-primary-dark hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 w-full sm:w-auto',
                ]
            ]);
    }
}

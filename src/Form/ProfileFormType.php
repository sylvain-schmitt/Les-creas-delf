<?php

namespace App\Form;

use App\Model\User;
use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\EmailType;
use Ogan\Form\Types\PasswordType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\Email;
use Ogan\Form\Constraint\MinLength;
use Ogan\Form\Constraint\UniqueEntity;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [
                    new Required('Le nom est obligatoire'),
                    new MinLength(2, 'Le nom doit contenir au moins 2 caractères')
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Votre nom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new Required('L\'email est obligatoire'),
                    new Email('L\'email n\'est pas valide'),
                    new UniqueEntity(User::class, 'email', 'Cet email est déjà utilisé.', $options['user_id'] ?? null)
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'votre@email.com'
                ]
            ])
            ->add('current_password', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'required' => false,
                'constraints' => [],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Requis pour changer le mot de passe'
                ]
            ])
            ->add('new_password', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => false,
                'constraints' => [],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Laisser vide pour ne pas changer'
                ]
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmer le nouveau mot de passe',
                'required' => false,
                'constraints' => [],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Confirmer le nouveau mot de passe'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

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
use Ogan\Form\Constraint\MinLength;
use Ogan\Form\Constraint\MaxLength;
use Ogan\Form\Constraint\Email;
use Ogan\Form\Constraint\EqualTo;
use Ogan\Form\Constraint\UniqueEntity;

class RegisterFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'constraints' => [
                    new Required('Name is required.'),
                    new MinLength(2, 'Name must be at least 2 characters.'),
                    new MaxLength(100, 'Name must not exceed 100 characters.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Votre nom complet',
                    'autofocus' => true
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Required('Email is required.'),
                    new Email('Please enter a valid email address.'),
                    new UniqueEntity(User::class, 'email', 'This email is already used.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'votre@email.com'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new Required('Password is required.'),
                    new MinLength(8, 'Password must be at least 8 characters.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Minimum 8 caractères'
                ]
            ])
            ->add('password_confirm', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'constraints' => [
                    new Required('Please confirm your password.'),
                    new EqualTo('password', 'Passwords do not match.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Retapez votre mot de passe'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer mon compte',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

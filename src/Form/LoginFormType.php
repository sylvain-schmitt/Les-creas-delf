<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\EmailType;
use Ogan\Form\Types\PasswordType;
use Ogan\Form\Types\CheckboxType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\Email;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Required('L\'email est requis.'),
                    new Email('Veuillez entrer une adresse email valide.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'votre@email.com',
                    'autofocus' => true
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new Required('Le mot de passe est requis.'),
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Votre mot de passe'
                ]
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'Se souvenir de moi',
                'constraints' => [],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Se connecter',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

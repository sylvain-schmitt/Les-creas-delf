<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\PasswordType;
use Ogan\Form\Types\SubmitType;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => true,
                'min' => 8,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Minimum 8 caractères',
                    'autofocus' => true
                ]
            ])
            ->add('password_confirm', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'required' => true,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'Retapez votre mot de passe'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Réinitialiser mon mot de passe',
                'attr' => ['class' => 'w-full btn-primary py-3']
            ]);
    }
}

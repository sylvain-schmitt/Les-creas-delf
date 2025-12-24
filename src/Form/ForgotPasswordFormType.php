<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\EmailType;
use Ogan\Form\Types\PasswordType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\Email as EmailConstraint;
use Ogan\Form\Constraint\MinLength;
use Ogan\Form\Constraint\EqualTo;

class ForgotPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $sendEmail = $options['send_email'] ?? false;

        $builder
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse email',
                'required' => true,
                'attr' => [
                    'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                    'placeholder' => 'votre@email.com',
                    'autofocus' => true
                ],
                'constraints' => [
                    new Required('L\'email est requis.'),
                    new EmailConstraint('Veuillez entrer un email valide.')
                ]
            ]);

        // If not sending email, add password fields for direct reset
        if (!$sendEmail) {
            $builder
                ->add('new_password', PasswordType::class, [
                    'label' => 'Nouveau mot de passe',
                    'required' => true,
                    'attr' => [
                        'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                        'placeholder' => 'Minimum 8 caractères'
                    ],
                    'constraints' => [
                        new Required('Le mot de passe est requis.'),
                        new MinLength(8, 'Le mot de passe doit contenir au moins 8 caractères.')
                    ]
                ])
                ->add('new_password_confirm', PasswordType::class, [
                    'label' => 'Confirmer le mot de passe',
                    'required' => true,
                    'attr' => [
                        'class' => 'w-full px-4 py-3 border border-default rounded-xl bg-card text-foreground focus:ring-2 focus:ring-primary focus:border-primary transition-all',
                        'placeholder' => 'Retapez votre mot de passe'
                    ],
                    'constraints' => [
                        new Required('La confirmation est requise.'),
                        new EqualTo('new_password', 'Les mots de passe ne correspondent pas.')
                    ]
                ]);
        }

        $buttonLabel = $sendEmail
            ? 'Envoyer le lien de réinitialisation'
            : 'Réinitialiser mon mot de passe';

        $builder->add('submit', SubmitType::class, [
            'label' => $buttonLabel,
            'attr' => ['class' => 'w-full btn-primary py-3']
        ]);
    }
}

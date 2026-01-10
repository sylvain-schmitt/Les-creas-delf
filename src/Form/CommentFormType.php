<?php

namespace App\Form;

use Ogan\Form\AbstractType;
use Ogan\Form\FormBuilder;
use Ogan\Form\Types\TextType;
use Ogan\Form\Types\EmailType;
use Ogan\Form\Types\TextareaType;
use Ogan\Form\Types\SubmitType;
use Ogan\Form\Constraint\Required;
use Ogan\Form\Constraint\Email;
use Ogan\Form\Constraint\MinLength;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options): void
    {
        $builder
            ->add('author_name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('Le nom est obligatoire'),
                    new MinLength(2, 'Le nom doit faire au moins 2 caractères')
                ]
            ])
            ->add('author_email', EmailType::class, [
                'label' => 'Votre email (ne sera pas publié)',
                'attr' => [
                    'placeholder' => 'votre@email.com',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('L\'email est obligatoire'),
                    new Email('L\'email n\'est pas valide')
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Partagez votre avis...',
                    'class' => 'w-full rounded-xl border-default bg-card px-4 py-3 text-foreground placeholder-muted focus:border-primary focus:ring-primary',
                ],
                'constraints' => [
                    new Required('Le commentaire est obligatoire'),
                    new MinLength(5, 'Le commentaire doit faire au moins 5 caractères')
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Poster le commentaire',
                'attr' => [
                    'class' => 'inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow-lg transition-all hover:bg-primary-dark hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
                ]
            ]);
    }
}

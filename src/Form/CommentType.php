<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate', null, [
                'attr' => [
                    'min' => 0,
                    'max' => 5,
                ],
            ])
            ->add('comment')
            ->add('author', EntityType::class, [
                'class' => 'App\Entity\User',
                'choice_label' => 'email',
                'disabled' => true, // Empêche l'utilisateur de changer la valeur
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
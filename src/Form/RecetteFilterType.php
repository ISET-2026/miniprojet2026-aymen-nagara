<?php

namespace App\Form;

use App\Entity\CategorieRecette;
use App\Entity\TagRecette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** Formulaire recherche filtres liste */
class RecetteFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre (partiel)',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieRecette::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Toutes les catégories',
            ])
            ->add('difficulte', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile',
                ],
                'required' => false,
                'placeholder' => 'Toutes',
            ])
            ->add('tag', EntityType::class, [
                'class' => TagRecette::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Tous les tags',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'get',
            'csrf_protection' => false,
        ]);
    }

    /** @inheritdoc */
    public function getBlockPrefix(): string
    {
        return 'f';
    }
}

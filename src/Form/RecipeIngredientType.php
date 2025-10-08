<?php

namespace App\Form;

use App\Entity\RecipeIngredient;
use App\Entity\Ingredient;
use App\Entity\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints as Assert;

class RecipeIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'label' => 'Ingrédient',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner un ingrédient.'
                    ])
                ]
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La position est obligatoire.'
                    ]),
                    new Assert\Type([
                        'type' => 'integer',
                        'message' => 'La position doit être un nombre entier.'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'La position doit être supérieure ou égale à 1.'
                    ])
                ]
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'required' => false,
                'scale' => 2,
                'constraints' => [
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'La quantité doit être un nombre.'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'La quantité doit être positive.'
                    ])
                ]
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'choice_label' => 'label',
                'label' => 'Unité',
                'required' => false,
                'placeholder' => 'Sélectionner une unité'
            ])
            ->add('displayQuantity', TextType::class, [
                'label' => 'Quantité d\'affichage',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'La quantité d\'affichage ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Note',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'La note ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeIngredient::class,
        ]);
    }
}
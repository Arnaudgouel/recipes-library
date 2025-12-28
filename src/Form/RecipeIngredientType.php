<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\RecipeIngredient;
use App\Entity\Unit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RecipeIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'label' => 'Ingrédient',
                'placeholder' => 'Choisir un ingrédient',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->orderBy('i.name', 'ASC');
                },
                'autocomplete' => true,
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'required' => false,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
            ])
            ->add('unit', EntityType::class, [
                'class' => Unit::class,
                'choice_label' => 'label',
                'label' => 'Unité',
                'required' => true,
                'placeholder' => 'Choisir une unité',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.label', 'ASC');
                },
                'constraints' => [
                    new NotNull(
                        message: 'Veuillez choisir une unité',
                    ),
                ],
            ])
            ->add('note', TextType::class, [
                'label' => 'Note',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: finement haché'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeIngredient::class,
        ]);
    }
}

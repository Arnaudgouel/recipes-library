<?php

namespace App\Form;

use App\Entity\RecipeStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'Étape n°',
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('instruction', TextareaType::class, [
                'label' => 'Instructions',
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('durationMin', IntegerType::class, [
                'label' => 'Durée (min)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeStep::class,
        ]);
    }
}


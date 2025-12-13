<?php

namespace App\Form;

use App\Entity\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code',
                'attr' => ['class' => 'form-control'],
                'disabled' => $options['is_edit'],
            ])
            ->add('label', TextType::class, [
                'label' => 'Libellé',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('pluralLabel', TextType::class, [
                'label' => 'Libellé pluriel',
                'attr' => ['class' => 'form-control'],
                'required' => false,
            ])
            ->add('kind', TextType::class, [
                'label' => 'Type',
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Unit::class,
            'is_edit' => false,
        ]);
    }
}


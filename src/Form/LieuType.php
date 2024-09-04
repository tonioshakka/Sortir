<?php

namespace App\Form;

use App\Entity\Lieu;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'still_input_form',
                ],
                'required' => false,
            ])
            ->add('rue', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rue',
                    'class' => 'still_input_form',
                ],
                'required' => false,
    ])
            ->add('latitude', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Latitutde',
                    'class' => 'still_input_form',
                ],
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'longitude',
                    'class' => 'still_input_form',
                ],
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Ville',
                    'class' => 'still_input_form',
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}

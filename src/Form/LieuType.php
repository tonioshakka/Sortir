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
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'required' => false
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'required' => false,
    ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
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

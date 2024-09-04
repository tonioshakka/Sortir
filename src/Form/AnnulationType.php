<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motif_annuler', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Motif d\'annulation',
                    'class' => 'still_input_form',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Annuler',
                'attr' => [
                    'placeholder' => 'Motif d\'annulation',
                    'class' => 'still_button',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => Sortie::class,
        ]);
    }
}

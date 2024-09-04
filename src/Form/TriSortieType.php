<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TriSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'label' => false,
                'placeholder' => 'CAMPUS',
                'required' => false,
                'attr' => [
                    'class' => 'still_input_form'
                ],
            ])
            ->add('search', SearchType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par nom...',
                    'class' => 'still_input_form'
                ]
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Entre ',
                'placeholder' => 'ENTRE',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'still_input_form',
                ],

            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => ' et ',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'still_input_form',
                ],

            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Sorties dont je suis l\'organisateur' => 'organisateur',
                    'Sorties auxquelles je suis inscrit' => 'inscrit',
                    'Sorties auxquelles je ne suis pas inscrit' => 'non_inscrit',
                    'Sorties passées' => 'passees',
                ],
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'still_button_form'
                ],
            ])
        ;


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default_site' => null, // Ajoute cette option pour passer le site par défaut
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
                'placeholder' => $options['default_site'] || 'Sélectionner un site',
                'required' => false,
            ])
            ->add('search', SearchType::class, [
                'label' => 'Le nom de la sortie contient',
                'required' => false,
                'attr' => ['placeholder' => 'Rechercher par nom...']
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Entre ',
                'widget' => 'single_text',
                'required' => false,

            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => ' et ',
                'widget' => 'single_text',
                'required' => false,

            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Sorties dont je suis l\'organisateur' => 'organisateur',
                    'Sorties auxquelles je suis inscrit' => 'inscrit',
                    'Sorties auxquelles je ne suis pas inscrit' => 'non_inscrit',
                    'Sorties passées' => 'passees',
                ],
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'default_site' => null, // Ajoute cette option pour passer le site par défaut
        ]);
    }
}

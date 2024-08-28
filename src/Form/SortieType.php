<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en minutes'
            ])
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de participants maximum',
            ])
            ->add('infosSortie')
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Selectionner une etat',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'required' => false,
                'choice_label' => function($lieu) {
                return $lieu->getNom() . ' - ' . $lieu->getRue() . ' - ' . $lieu->getVille()->getNom();
                },
                'placeholder' => '-- Selectionner une lieu --',
            ])
            ->add('lieuNew', LieuType::class, [
                'label' => 'Ajouter un lieu',
                'mapped' => false,
                'property_path' => 'lieu',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                if (!empty($data['lieuNew']['name'])) {
                    $form->remove('lieu');
                    $form->add('lieuNew', LieuType::class, [
                        'required' => true,
                        'mapped' => true,
                        'property_path' => 'lieuNew'
                    ]);
                }
            })
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'cascade_validation' => true,
        ]);
    }
}

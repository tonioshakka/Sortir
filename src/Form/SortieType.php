<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder

            ->add('nom', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'still_input_form',
                ],
            ])
            ->add('dateHeureDebut', null, [
                'label' => 'Date de début',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('duree', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Durée en minutes',
                    'class' => 'still_input_form',
                ],
            ])
            ->add('dateLimiteInscription', null, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nombre d\'inscription max',
                    'class' => 'still_input_form',
                ],
                'required' => false,
            ])
            ->add('infosSortie', TextareaType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Information sur la sortie',
                    'class' => 'still_input_form',
                ]
            ])
            ->add('etat', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Etat',
                'attr' => [
                    'class' => 'still_choice_form',
                ],
            ])
            ->add('lieu', EntityType::class, [
                'label' => false,
                'class' => Lieu::class,
                'required' => false,
                'mapped' => false,
                'choice_label' => function($lieu) {
                return $lieu->getNom() . ' - ' . $lieu->getRue() . ' - ' . $lieu->getVille();
                },
                'placeholder' => 'Lieu',
                'attr' => [
                    'class' => 'still_choice_form',
                ],
            ])
            ->add('lieuNew', LieuType::class, [
                'label' => 'Ajoutez un lieu',
                'required' => false,
                'mapped' => true,
                'property_path' => 'lieu',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                if ($data['lieuNew']['nom'] || $data['lieuNew']['latitude']) {

                    $form->add('lieuNew', LieuType::class, [
                        'label' => 'Ajoutez un lieu',
                        'required' => false,
                        'mapped' => true,
                        'property_path' => 'lieu',
                    ]);
                }
                else {
                    $form->add('lieu', EntityType::class, [
                        'class' => Lieu::class,
                        'required' => false,
                        'mapped' => true,
                        'choice_label' => function($lieu) {
                            return $lieu->getNom() . ' - ' . $lieu->getRue() . ' - ' . $lieu->getVille();
                        },
                        'placeholder' => '-- Sélectionnez un lieu --',
                    ]);
                    $form->add('lieuNew', LieuType::class, [
                        'label' => 'Ajoutez un lieu',
                        'required' => false,
                        'mapped' => false,
                        'property_path' => 'lieu',
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'allow_extra_fields' => true,
            'cascade_validation' => true,
        ]);
    }
}

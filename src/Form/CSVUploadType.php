<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\File;

    class CSVUploadType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('csv_file', FileType::class, [
                    'label' => 'Télécharger un fichier CSV',
                    'mapped' => false,
                    'required' => true,
                    'constraints' => [
                    new File([
                        'mimeTypes' => [
                        'text/csv',
                        'text/plain',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier CSV valide',
                    ])
                    ],
                    ]);
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults(['data_class' => null]);
        }
    }
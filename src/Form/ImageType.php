<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichFileType::class,  [
                'label' => 'Profile Picture',
                'required' => false, // Le champ n'est pas requis pour ne pas forcer le téléchargement
                'allow_delete' => true,  // Permet de supprimer l'image
                'download_uri' => false,  // Lien de téléchargement de l'image existante
                'delete_label' => 'Supprimer l\'image', // Texte pour le bouton de suppression
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}

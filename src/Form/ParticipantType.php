<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'choices' =>[
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ],
                    'multiple' => true,
                    'expanded' => true,
            ])
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('actif')
            ->add('image', ImageType::class, [
                'label' => 'Profile Picture',
                // Configuration spécifique si besoin
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
            ]);
        if ($options['password_field']) {
            $builder->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'mapped' => true,
                'label' => 'Password',
                'attr' => ['autocomplete' => 'new-password'],
                'invalid_message' => 'Le mot de passe ne correspond pas',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
            ]);
        }
        if ($options['email_field']) {
            $builder->add('email', EmailType::class, [
                'required' =>true,
                'label' => 'Email',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'password_field' => true,
            'email_field' => true,
        ]);
    }
}

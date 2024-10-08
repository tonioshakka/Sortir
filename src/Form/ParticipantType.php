<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\HtmlSanitizer\Type\TextTypeHtmlSanitizerExtension;
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
                    'label' => false,
                    'multiple' => true,
                    'expanded' => true,
            ])
            ->add('nom', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => False,
                'attr' => [
                    'placeholder' => 'Nom',
                    'class' => 'still_input_form'
                ],

            ])
            ->add('prenom', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => False,
                'attr' => [
                    'placeholder' => 'Prénom',
                    'class' => 'still_input_form'
                ],
            ])
            ->add('telephone', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => False,
                'attr' => [
                    'placeholder' => 'Téléphone',
                    'class' => 'still_input_form'
                ],
            ])
            ->add('actif')
            ->add('image', ImageType::class, [
                'required'=>'false',
                'label' => false,
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
                'first_options' => [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                        'class' => 'still_input_form'
                    ],
                ],
                'second_options' => ['label' => false,
                    'attr' => [
                        'placeholder' => 'Confirmez le mot de passe',
                        'class' => 'still_input_form'
                    ],
                ],

            ]);
        }
        if ($options['email_field']) {
            $builder->add('email', EmailType::class, [
                'required' =>true,
                'label' => False,
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'still_input_form'
                ],

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

<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username',TextType::class,[
                'disabled' => true,
            ])
            ->add('old_password', PasswordType::class,
                [
                    'label' => 'Mon mot de passe actuel',
                    'mapped'=>false,
                ])


            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped'=>false,
                'invalid_message' => 'Le mot de passe et la confirmation doivent être identique', 'label'=>'Mon nouveau mot de passe',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' =>
                    [
                        'label' => 'Mon nouveau mot de passe',

                    ],
                'second_options' =>
                    [
                        'label' => 'Confirmez mon nouveau mot de passe',
                    ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

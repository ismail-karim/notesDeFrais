<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdatePassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('password', PasswordType::class, [
                'label'=> 'Ancien mot de passe',
                'mapped'=> false,
                'attr'=>[
                    'placeholder' => 'Veuillez saisir votre ancien mot de passe'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label'=>'Nouveau mot de passe',
                'required'=> true,
            ])
            ->add('new_password', RepeatedType::class, [
                'type'=> PasswordType::class,
                'invalid_message'=>'Le mot de passe ne correspond pas !',
                'options'=>['attr'=>['class'=>'password-field']],
                'required'=>true,
                'first_options'=>[
                    'label'=> 'Nouveau mot de passe',
                    'attr' => ['placeholder' => 'Veuillez saisir un nouveau mot de passe']
                ],
                'second_options'=>[
                    'label'=>'Confirmer le nouveau mot de passe',
                    'attr'=> ['placeholder' =>'Veuillez confirmer le nouveau mot de passe']
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['label'=>'username'])
            ->add('nom', TextType::class, ['label'=> 'Nom'])
            ->add('prenom', TextType::class, ['label'=> 'Prénom'])
            ->add('telephone', TextType::class, ['label'=>'Téléphone', 'required'=>false])
            ->add('email', EmailType::class, ['label'=>'Email', 'required'=>true])
            ->add('imageFile', FileType::class, ['label'=>'Image', 'required'=>false])
            ->add('actif', CheckboxType::class, ['label'=>'Actif', 'required'=>false])
           ->add('roles', ChoiceType::class,[
               'multiple'=>false,
                   'choices'=>[
                       'Formateur'=>'ROLE_FORMATEUR',
                       'Comptable'=>'ROLE_COMPTA',
                       'Administrateur'=>'ROLE_ADMIN'
                   ]
           ]);

       $builder->get('roles')->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transforme le tableau en string
                    return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                    // transforme le string en tableau
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

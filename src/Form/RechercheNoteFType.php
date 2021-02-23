<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\RechercheNote;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheNoteFType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'label'=> 'Date: ',
                'required'=>false,
                'placeholder' => [
                    'year' => 'Années', 'month' => 'Mois', 'day' => 'Jours',
                ]
            ])
            ->add('etat', EntityType::class, [
                'choice_label'=>'libelle',
                'class'=>Etat::class,
                'label'=>'Etat: ',
                'required'=>false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RechercheNote::class,
        ]);
    }
}

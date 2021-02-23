<?php

namespace App\Form;

use App\Entity\Depense;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddDepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', ChoiceType::class, ['label'=>'Dépense',
                'choices'=>[
                    'Carburant'=>[
                        'Essence'=>'Essence',
                        'Diesel'=>'Diesel',
                        'GPL'=>'GPL'
                    ],
                    'Transport'=>[
                        'Train'=>'Train',
                        'Taxi'=>'Taxi'
                    ],
                    'Repas'=>[
                        'Midi'=>'Repas midi',
                        'Soir'=>'Repas soir'
                    ],
                    'Hotel'=>'Hotel',
                    'Diver'=>'Divers'
                ]
            ])
            ->add('date', DateType::class, [
                'label'=>'Date',
                'placeholder' => [
                'year' => 'Années', 'month' => 'Mois', 'day' => 'Jours',
                    ]
            ])
            ->add('prix', MoneyType::class)
            ->add('imageFile', FileType::class, ['label'=>'Justificatif','required'=> true]);

        $builder->get('libelle')->addModelTransformer(new CallbackTransformer(
            function ($libelleArray) {
                // transforme le tableau en string
                return count($libelleArray)? $libelleArray[0]: null;
            },
            function ($libelleArray) {
                // transforme le string en tableau
                return [$libelleArray];
            }
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
        ]);
    }
}

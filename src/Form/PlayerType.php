<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('position', ChoiceType::class, [
                'choices' => [
                    'Gardien' => 'Gardien',
                    'Défenseur' => 'Défenseur',
                    'Milieu' => 'Milieu',
                    'Attaquant' => 'Attaquant',
                ],
                'placeholder' => 'Sélectionner une position',
                'expanded' => false, // false = menu déroulant (true = boutons radio)
                'multiple' => false, // false = un seul choix possible
            ])
            ->add('club')
            ->add('shirtNumber', IntegerType::class, [
                'label' => 'Numéro de tenue',
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 99]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}

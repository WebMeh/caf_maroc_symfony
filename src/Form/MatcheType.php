<?php

namespace App\Form;

use App\Entity\Matche;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatcheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('team1', EntityType::class, [
            'class' => Team::class,
            'choice_label' => 'name',
            'label' => 'Équipe 1',
            'attr' => ['class' => 'form-select']
        ])
        ->add('team2', EntityType::class, [
            'class' => Team::class,
            'choice_label' => 'name',
            'label' => 'Équipe 2',
            'attr' => ['class' => 'form-select']
        ])
        ->add('date', DateTimeType::class, [
            'label' => 'Date et heure du match',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control']
        ])
        ->add('stadium', TextType::class, [
            'label' => 'Stade',
            'attr' => ['class' => 'form-control']
        ])
        ->add('phase', ChoiceType::class, [
            'label' => 'Phase du tournoi',
            'choices' => [
                'Phase de groupes' => 'Groupes',
                'Quarts de finale' => 'Quarts',
                'Demi-finales' => 'Demi',
                'Finale' => 'Finale',
            ],
            'attr' => ['class' => 'form-select']
        ])
        ->add('score1', IntegerType::class, [
            'label' => 'Score Équipe 1',
            'required' => false,
            'attr' => ['class' => 'form-control', 'min' => 0], 
            'data' => 0, // Valeur par défaut
        ])
        ->add('score2', IntegerType::class, [
            'label' => 'Score Équipe 2',
            'required' => false,
            'attr' => ['class' => 'form-control', 'min' => 0], 
            'data' => 0, // Valeur par défaut
        ]); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matche::class,
        ]);
    }
}

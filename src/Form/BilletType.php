<?php

namespace App\Form;

use App\Entity\Billet;
use App\Entity\Matche;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BilletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix')
            ->add('placesDisponibles')
            ->add('matche', EntityType::class, [
                'class' => Matche::class,
                'choice_label' => function (Matche $match) {
                    return sprintf(
                        '%s vs %s - %s',
                        $match->getTeam1()->getName(),
                        $match->getTeam2()->getName(),
                        $match->getDate()->format('d/m/Y H:i')
                    );
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.date', 'ASC'); // Trier par date
                },
                'attr' => ['class' => 'form-select'],
                'placeholder' => 'Sélectionner un match',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Billet::class,
        ]);
    }
}

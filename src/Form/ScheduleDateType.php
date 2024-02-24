<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\ScheduleDate;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ScheduleDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', CoreType\DateType::class, [
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'empty_data' => null,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('vehicle', EntityType::class, [
                'required' => true,
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('driver', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('collector', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ScheduleDate::class,
        ]);
    }
}

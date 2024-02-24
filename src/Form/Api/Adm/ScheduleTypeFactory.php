<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\Schedule;
use App\Entity\Vehicle;
use App\Form\ScheduleDateType;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ScheduleTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Schedule::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class)
            ->add('tableCode', CoreType\TextType::class)
            ->add('sequence', CoreType\TextType::class)
            ->add('startsAt', CoreType\DateTimeType::class, [
                    'format' => 'HH:mm',
                    'widget' => 'single_text',
            ])
            ->add('endsAt', CoreType\DateTimeType::class, [
                    'format' => 'HH:mm',
                    'widget' => 'single_text',
            ])
            ->add('dataValidity', CoreType\DateType::class, [
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
            ])
            ->add('driver', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('collector', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('line', EntityType::class, [
                'class' => Line::class,
                'choice_value' => 'identifier',
            ])
            ->add('modality', CoreType\ChoiceType::class, [
                'choices' => [
                    'STARTING_OPERATION' => 'STARTING_OPERATION',
                    'TRIP' => 'TRIP',
                    'MOVEMENT' => 'MOVEMENT',
                    'RESERVED' => 'RESERVED',
                    'CLOSING_OPERATION' => 'CLOSING_OPERATION',
                ],
            ])
            ->add('weekInterval', CoreType\ChoiceType::class, [
                'choices' => [
                    'WEEKDAY' => 'WEEKDAY',
                    'SATURDAY' => 'SATURDAY',
                    'SUNDAY' => 'SUNDAY',
                ],
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('dates', CoreType\CollectionType::class, [
                'mapped' => false,
                'entry_type' => ScheduleDateType::class,
                'allow_add' => true,
                'delete_empty' => true,
            ])
        ;

        return $this;
    }
}

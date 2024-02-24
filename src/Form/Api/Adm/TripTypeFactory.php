<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\Obd;
use App\Entity\ScheduleDate;
use App\Entity\Trip;
use App\Entity\TripModality;
use App\Entity\TripStatus;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class TripTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Trip::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('starts_at', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'widget' => 'single_text',
            ])
            ->add('ends_at', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'widget' => 'single_text',
            ])
            ->add('line', EntityType::class, [
                'class' => Line::class,
                'choice_value' => 'identifier',
            ])
            ->add('collector', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('driver', EntityType::class, [
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('obd', EntityType::class, [
                'class' => Obd::class,
                'choice_value' => 'identifier',
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('status', EntityType::class, [
                'class' => TripStatus::class,
                'choice_value' => 'identifier',
            ])
            ->add('modality', EntityType::class, [
                'class' => TripModality::class,
                'choice_value' => 'identifier',
            ])
            ->add('scheduleDate', EntityType::class, [
                'class' => ScheduleDate::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\EventStatus;
use App\Entity\Line;
use App\Entity\Sector;
use App\Entity\Trip;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class EventEditTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Event::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('comment', CoreType\TextType::class)
            ->add('local', CoreType\TextType::class)
            ->add('action', CoreType\TextType::class)
            ->add('start', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'widget' => 'single_text',
            ])
            ->add('end', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy HH:mm:ss',
                'widget' => 'single_text',
            ])
            ->add('vehicle', EntityType::class, [
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('category', EntityType::class, [
                'class' => EventCategory::class,
                'choice_value' => 'identifier',
            ])
            ->add('line', EntityType::class, [
                'class' => Line::class,
                'choice_value' => 'identifier',
            ])
            ->add('trip', EntityType::class, [
                'class' => Trip::class,
                'choice_value' => 'identifier',
            ])
            ->add('employee', EntityType::class, [
                'class' => Employee::class,
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
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_value' => 'identifier',
            ])
            ->add('status', EntityType::class, [
                'class' => EventStatus::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

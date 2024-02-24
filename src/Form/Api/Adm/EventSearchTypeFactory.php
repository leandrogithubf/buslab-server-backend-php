<?php

namespace App\Form\Api\Adm;

use App\Entity\Employee;
use App\Entity\EventCategory;
use App\Entity\Sector;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('start', CoreType\DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd H:mm:ss',
            ])
            ->add('end', CoreType\DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('days', CoreType\TextType::class)
            ->add('sequence', CoreType\CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'entry_type' => CoreType\DateTimeType::class,
                'entry_options' => [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                ],
            ])
            ->add('occurrenceType', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => EventCategory::class,
                'choice_value' => 'identifier',
            ])
            ->add('vehicle', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('sector', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Sector::class,
                'choice_value' => 'identifier',
            ])
            ->add('collaborators', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('dateStart', CoreType\DateTimeType::class, [
                    'format' => 'dd/MM/yyyy H:mm',
                    'widget' => 'single_text',
            ])
            ->add('dateEnd', CoreType\DateTimeType::class, [
                    'format' => 'dd/MM/yyyy H:mm',
                    'widget' => 'single_text',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['vehicle']) && isset($data['vehicle'][0]) && '' == $data['vehicle'][0]) {
                    $data['vehicle'] = [];
                    $event->setData($data);
                }

                if (isset($data['trip']) && isset($data['trip'][0]) && '' == $data['trip'][0]) {
                    $data['trip'] = [];
                    $event->setData($data);
                }

                if (isset($data['collaborators']) && isset($data['collaborators'][0]) && '' == $data['collaborators'][0]) {
                    $data['collaborators'] = [];
                    $event->setData($data);
                }

                if (isset($data['eventType']) && isset($data['eventType'][0]) && '' == $data['eventType'][0]) {
                    $data['eventType'] = [];
                    $event->setData($data);
                }

                if (isset($data['sector']) && isset($data['sector'][0]) && '' == $data['sector'][0]) {
                    $data['sector'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCategory;
use App\Entity\Line;
use App\Entity\Sector;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class TelemetryOccurrenceSearchTypeFactory extends ApiSearchFormTypeFactory
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
            ->add('line', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Line::class,
                'choice_value' => 'identifier',
            ])
            ->add('driver', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Employee::class,
                'choice_value' => 'identifier',
            ])
            ->add('vehicle', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('company', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('sector', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Sector::class,
                'choice_value' => 'identifier',
            ])
            ->add('occurrenceType', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => EventCategory::class,
                'choice_value' => 'identifier',
            ])
            ->add('direction', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['company']) && isset($data['company'][0]) && '' == $data['company'][0]) {
                    $data['company'] = [];
                    $event->setData($data);
                }
                if (isset($data['line']) && isset($data['line'][0]) && '' == $data['line'][0]) {
                    $data['line'] = [];
                    $event->setData($data);
                }
                if (isset($data['vehicle']) && isset($data['vehicle'][0]) && '' == $data['vehicle'][0]) {
                    $data['vehicle'] = [];
                    $event->setData($data);
                }
                if (isset($data['driver']) && isset($data['driver'][0]) && '' == $data['driver'][0]) {
                    $data['driver'] = [];
                    $event->setData($data);
                }
                if (isset($data['sector']) && isset($data['sector'][0]) && '' == $data['sector'][0]) {
                    $data['sector'] = [];
                    $event->setData($data);
                }
                if (isset($data['occurrenceType']) && isset($data['occurrenceType'][0]) && '' == $data['occurrenceType'][0]) {
                    $data['occurrenceType'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Line;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class VehicleLocationSearchTypeFactory extends ApiSearchFormTypeFactory
{
    protected $method = 'GET';

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
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
            ->add('company', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('vehicle', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
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

                if (isset($data['driver']) && isset($data['driver'][0]) && '' == $data['driver'][0]) {
                    $data['driver'] = [];
                    $event->setData($data);
                }

                if (isset($data['vehicle']) && isset($data['vehicle'][0]) && '' == $data['vehicle'][0]) {
                    $data['vehicle'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

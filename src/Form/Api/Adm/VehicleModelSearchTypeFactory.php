<?php

namespace App\Form\Api\Adm;

use App\Entity\VehicleBrand;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class VehicleModelSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->add('brand', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => VehicleBrand::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['description']) && isset($data['description'][0]) && '' == $data['description'][0]) {
                    $data['description'] = [];
                    $event->setData($data);
                }

                if (isset($data['brand']) && isset($data['brand'][0]) && '' == $data['brand'][0]) {
                    $data['brand'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Obd;
use App\Entity\VehicleBrand;
use App\Entity\VehicleModel;
use App\Entity\VehicleStatus;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class VehicleSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('prefix', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->add('plate', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->add('obd', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Obd::class,
                'choice_value' => 'identifier',
            ])
            ->add('status', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => VehicleStatus::class,
                'choice_value' => 'id',
            ])
            ->add('company', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('model', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => VehicleModel::class,
                'choice_value' => 'identifier',
            ])
            ->add('brand', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => VehicleBrand::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['model']) && isset($data['model'][0]) && '' == $data['model'][0]) {
                    $data['model'] = [];
                    $event->setData($data);
                }

                if (isset($data['brand']) && isset($data['brand'][0]) && '' == $data['brand'][0]) {
                    $data['brand'] = [];
                    $event->setData($data);
                }

                if (isset($data['company']) && isset($data['company'][0]) && '' == $data['company'][0]) {
                    $data['company'] = [];
                    $event->setData($data);
                }

                if (isset($data['obd']) && isset($data['obd'][0]) && '' == $data['obd'][0]) {
                    $data['obd'] = [];
                    $event->setData($data);
                }
                if (isset($data['status']) && isset($data['status'][0]) && '' == $data['status'][0]) {
                    $data['status'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

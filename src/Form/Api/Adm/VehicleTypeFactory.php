<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Obd;
use App\Entity\Vehicle;
use App\Entity\VehicleModel;
use App\Entity\VehicleStatus;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class VehicleTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Vehicle::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('plate', CoreType\TextType::class)
            ->add('prefix', CoreType\TextType::class)
            ->add('consumptionTarget', CoreType\TextType::class)
            ->add('startOperation', CoreType\DateType::class, [
                'format' => 'MM/yyyy',
                'widget' => 'single_text',
            ])
            ->add('manufacture', CoreType\TextType::class)
            ->add('chassi', CoreType\TextType::class)
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->add('obd', EntityType::class, [
                'class' => Obd::class,
                'choice_value' => 'identifier',
            ])
            ->add('model', EntityType::class, [
                'class' => VehicleModel::class,
                'choice_value' => 'identifier',
            ])
            ->add('status', EntityType::class, [
                'class' => VehicleStatus::class,
                'choice_value' => 'id',
            ])
            ->add('manufactoreBodywork', CoreType\TextType::class)
            ->add('bodywork', CoreType\TextType::class)
            ->add('doorsNumber', CoreType\TextType::class)
            ->add('seats', CoreType\TextType::class)
            ->add('standing', CoreType\TextType::class)
            ->add('periodicInspection', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
            ])
        ;

        return $this;
    }
}

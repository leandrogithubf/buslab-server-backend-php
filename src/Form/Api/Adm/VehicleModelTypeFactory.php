<?php

namespace App\Form\Api\Adm;

use App\Entity\VehicleBrand;
use App\Entity\VehicleModel;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class VehicleModelTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = VehicleModel::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class)
            ->add('fuelDensity', CoreType\TextType::class)
            ->add('airFuelRatio', CoreType\TextType::class)
            ->add('efficiency', CoreType\TextType::class)
            ->add('volume', CoreType\TextType::class)
            ->add('ect', CoreType\TextType::class)
            ->add('iat', CoreType\TextType::class)
            ->add('brand', EntityType::class, [
                'class' => VehicleBrand::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

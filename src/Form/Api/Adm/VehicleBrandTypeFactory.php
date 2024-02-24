<?php

namespace App\Form\Api\Adm;

use App\Entity\VehicleBrand;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class VehicleBrandTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = VehicleBrand::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class)
        ;

        return $this;
    }
}

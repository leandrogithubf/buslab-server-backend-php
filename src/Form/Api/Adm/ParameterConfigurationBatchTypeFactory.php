<?php

namespace App\Form\Api\Adm;

use App\Form\ParameterConfigurationType;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ParameterConfigurationBatchTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = [];

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('values', CoreType\CollectionType::class, [
                'entry_type' => ParameterConfigurationType::class,
                'required' => true,
                'allow_add' => true,
            ])
        ;

        return $this;
    }
}

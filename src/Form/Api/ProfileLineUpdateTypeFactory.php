<?php

namespace App\Form\Api;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ProfileLineUpdateTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = [];

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('lines', CoreType\CollectionType::class, [
                'entry_type' => CoreType\TextType::class,
                'required' => true,
                'allow_add' => true,
            ])
        ;

        return $this;
    }
}

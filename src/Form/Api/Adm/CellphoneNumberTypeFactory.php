<?php

namespace App\Form\Api\Adm;

use App\Entity\CellphoneNumber;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class CellphoneNumberTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = CellphoneNumber::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('number', CoreType\TextType::class)
        ;

        return $this;
    }
}

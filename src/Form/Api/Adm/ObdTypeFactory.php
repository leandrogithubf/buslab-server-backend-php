<?php

namespace App\Form\Api\Adm;

use App\Entity\CellphoneNumber;
use App\Entity\Company;
use App\Entity\Obd;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ObdTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Obd::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('serial', CoreType\TextType::class)
            ->add('version', CoreType\TextType::class)
            ->add('cellphoneNumber', EntityType::class, [
                'class' => CellphoneNumber::class,
                'choice_value' => 'identifier',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Consumption;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ConsumptionTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Consumption::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('consumption', CoreType\TextType::class)
            ->add('date', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

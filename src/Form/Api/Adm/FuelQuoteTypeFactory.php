<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\FuelQuote;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class FuelQuoteTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = FuelQuote::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('value', CoreType\TextType::class)
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

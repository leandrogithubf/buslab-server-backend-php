<?php

namespace App\Form\Api;

use App\Entity\City;
use App\Entity\Company;
use App\Entity\Role;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class CompanyTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Company::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class)
            ->add('streetCode', CoreType\TextType::class)
            ->add('streetName', CoreType\TextType::class)
            ->add('streetNumber', CoreType\TextType::class)
            ->add('streetComplement', CoreType\TextType::class)
            ->add('streetDistrict', CoreType\TextType::class)
            ->add('color', CoreType\TextType::class)
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_value' => 'identifier',
            ])
            ->add('rolePlan', EntityType::class, [
                'class' => Role::class,
                'choice_value' => 'id',
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\CompanyPlace;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyPlaceTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = CompanyPlace::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('description', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('markers', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;

        return $this;
    }
}

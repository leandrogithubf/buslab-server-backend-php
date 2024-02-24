<?php

namespace App\Form\Api\Adm;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Validator\Constraints as Assert;

class CellphoneNumberSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('number', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 20,
                    ]),
                ],
            ])
        ;

        return $this;
    }
}

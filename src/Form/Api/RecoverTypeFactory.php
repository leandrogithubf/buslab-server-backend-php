<?php

namespace App\Form\Api;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecoverTypeFactory extends ApiFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('user', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('recaptcha_token', CoreType\TextType::class, [
                'required' => false,
                'mapped' => false,
            ])
        ;

        return $this;
    }
}

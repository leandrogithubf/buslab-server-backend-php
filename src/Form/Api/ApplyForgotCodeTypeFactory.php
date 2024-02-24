<?php

namespace App\Form\Api;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class ApplyForgotCodeTypeFactory extends ApiFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('password', TextType::class, [
                'attr' => [
                    'maxlength' => 30,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 8,
                        'max' => 30,
                    ]),
                ],
            ])
            ->add('recaptcha_token', TextType::class, [
                'required' => false,
                'mapped' => false,
            ])
        ;

        return $this;
    }
}

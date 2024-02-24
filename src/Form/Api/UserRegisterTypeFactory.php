<?php

namespace App\Form\Api;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class UserRegisterTypeFactory extends UserEditTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        parent::addCustomFields();

        $this->builder
            ->add('password', CoreType\PasswordType::class, [
                'required' => true,
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api;

use App\Entity\User;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class UserPasswordTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = User::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('password', CoreType\PasswordType::class, [
                'required' => true,
            ])
        ;

        return $this;
    }
}

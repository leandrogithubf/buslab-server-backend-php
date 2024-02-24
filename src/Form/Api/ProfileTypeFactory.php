<?php

namespace App\Form\Api;

use App\Entity\User;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProfileTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = User::class;
    private $password;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('name', CoreType\TextType::class, [
                'required' => true,
            ])
            ->add('email', CoreType\EmailType::class, [
                'required' => true,
            ])
            ->add('password', CoreType\PasswordType::class, [
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['password'])) {
                    $this->password = $data['password'];
                }
            })
        ;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }


}

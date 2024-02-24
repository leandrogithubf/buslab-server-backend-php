<?php

namespace App\Form\Api;

use App\Entity\Company;
use App\Entity\User;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserEditTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = User::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('name', CoreType\TextType::class, [
                'required' => true,
            ])
            ->add('cellphone', CoreType\TextType::class, [
                'required' => true,
            ])
            ->add('email', CoreType\EmailType::class, [
                'required' => true,
            ])
            ->add('documentNumber', CoreType\TextType::class, [
                'required' => true,
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['cellphone'])) {
                    $data['cellphone'] = StringUtils::onlyNumbers($data['cellphone']);
                    $event->setData($data);
                }

                if (isset($data['documentNumber'])) {
                    $data['documentNumber'] = StringUtils::onlyNumbers($data['documentNumber']);
                    $event->setData($data);
                }

                if (isset($data['company']) && isset($data['company'][0]) && '' == $data['company'][0]) {
                    $data['company'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

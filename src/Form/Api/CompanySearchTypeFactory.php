<?php

namespace App\Form\Api;

use App\Entity\City;
use App\Entity\Role;
use App\Entity\State;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class CompanySearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('descriptionName', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->add('city', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => City::class,
                'choice_value' => 'identifier',
            ])
            ->add('state', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => State::class,
                'choice_value' => 'identifier',
            ])
            ->add('rolePlan', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Role::class,
                'choice_value' => 'description',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['city']) && isset($data['city'][0]) && '' == $data['city'][0]) {
                    $data['city'] = [];
                    $event->setData($data);
                }

                if (isset($data['state']) && isset($data['state'][0]) && '' == $data['state'][0]) {
                    $data['state'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

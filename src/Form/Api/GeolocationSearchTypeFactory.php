<?php

namespace App\Form\Api;

use App\Entity\City;
use App\Entity\State;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class GeolocationSearchTypeFactory extends ApiFormTypeFactory
{
    protected $method = 'GET';

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('search', CoreType\TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                    ]),
                ],
            ])
            ->add('city', EntityType::class, [
                'required' => false,
                'class' => City::class,
                'choice_value' => 'identifier',
            ])
            ->add('state', EntityType::class, [
                'required' => false,
                'class' => State::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
            })
        ;

        return $this;
    }
}

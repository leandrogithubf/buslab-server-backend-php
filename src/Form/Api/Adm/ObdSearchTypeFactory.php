<?php

namespace App\Form\Api\Adm;

use App\Entity\CellphoneNumber;
use App\Entity\Company;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

class ObdSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('serial', CoreType\TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new Assert\Length([
                            'min' => 1,
                            'max' => 90,
                        ]),
                    ],
                ])
            ->add('version', CoreType\TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 1,
                        'max' => 90,
                    ]),
                ],
            ])
            ->add('cellphoneNumber', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => CellphoneNumber::class,
                'choice_value' => 'identifier',
            ])
            ->add('company', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['cellphoneNumber']) && isset($data['cellphoneNumber'][0]) && '' == $data['cellphoneNumber'][0]) {
                    $data['cellphoneNumber'] = [];
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

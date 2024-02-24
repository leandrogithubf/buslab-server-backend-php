<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ConsumptionSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('date', CoreType\DateType::class, [
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
            ])
            ->add('company', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Company::class,
                'choice_value' => 'identifier',
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['company']) && isset($data['company'][0]) && '' == $data['company'][0]) {
                    $data['company'] = [];
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

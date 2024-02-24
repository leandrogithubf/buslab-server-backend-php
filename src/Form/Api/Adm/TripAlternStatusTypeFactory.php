<?php

namespace App\Form\Api\Adm;

use App\Entity\Trip;
use App\Entity\TripStatus;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TripAlternStatusTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Trip::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('status', EntityType::class, [
                'class' => TripStatus::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Trip;
use App\Entity\TripModality;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TripAlternModalityTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Trip::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('modality', EntityType::class, [
                'class' => TripModality::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

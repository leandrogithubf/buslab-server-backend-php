<?php

namespace App\Form\Api;

use App\Entity\Sector;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventCategorySearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiSearchFormTypeFactory
    {
        $this->builder
            ->add('sector', EntityType::class, [
                'required' => false,
                'class' => Sector::class,
                'choice_value' => 'identifier',
            ])
        ;

        return $this;
    }
}

<?php

namespace App\Form\Api\Adm;

use App\Entity\Line;
use App\Form\LinePointType;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class LinePointsTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Line::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('points', CoreType\CollectionType::class, [
                'entry_type' => LinePointType::class,
                'allow_add' => true,
                'delete_empty' => true,
            ])
        ;

        return $this;
    }
}

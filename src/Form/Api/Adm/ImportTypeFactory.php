<?php

namespace App\Form\Api\Adm;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ImportTypeFactory extends ApiFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('file', CoreType\FileType::class);

        return $this;
    }
}

<?php

namespace App\Form\Api;

use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class ProfileNotificationUpdateTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = [];

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('eventCategory', CoreType\CollectionType::class, [
                'entry_type' => CoreType\TextType::class,
                'required' => true,
                'allow_add' => true,
            ])
            ->add('isEnabled', CoreType\ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'enabled' => true,
                    'disabled' => false,
                ],
            ])
        ;

        return $this;
    }
}

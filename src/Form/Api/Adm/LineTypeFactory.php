<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Line;
use App\Form\LinePointType;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Validator\Constraints\NotBlank;

class LineTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Line::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('code', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('description', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('passage', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('maxSpeed', CoreType\TextType::class)
            ->add('direction', CoreType\ChoiceType::class, [
                'choices' => [
                    'GOING' => 'GOING',
                    'RETURN' => 'RETURN',
                    'CIRCULATE' => 'CIRCULATE',
                ],
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('points', CoreType\CollectionType::class, [
                'entry_type' => LinePointType::class,
                'allow_add' => true,
                'delete_empty' => true,
            ])
        ;

        return $this;
    }
}

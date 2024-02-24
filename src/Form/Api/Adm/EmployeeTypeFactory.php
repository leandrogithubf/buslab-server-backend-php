<?php

namespace App\Form\Api\Adm;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EmployeeModality;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Utils\String\StringUtils;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeeTypeFactory extends ApiFormTypeFactory
{
    protected $dataClass = Employee::class;

    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('name', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('code', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('company', EntityType::class, [
                'class' => Company::class,
                'choice_value' => 'identifier',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('modality', EntityType::class, [
                'class' => EmployeeModality::class,
                'choice_value' => 'identifier',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('cnh', CoreType\TextType::class)
            ->add('cnhExpiration', CoreType\DateType::class, [
                'format' => 'MM/yyyy',
                'widget' => 'single_text',
            ])
            ->add('cellphone', CoreType\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if (isset($data['cellphone'])) {
                    $data['cellphone'] = StringUtils::onlyNumbers($data['cellphone']);
                    $event->setData($data);
                }
            })
        ;

        return $this;
    }
}

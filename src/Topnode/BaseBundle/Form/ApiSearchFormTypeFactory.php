<?php

namespace App\Topnode\BaseBundle\Form;

use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

class ApiSearchFormTypeFactory extends ApiFormTypeFactory
{
    protected $method = 'GET';

    /**
     * @param FormFactoryInterface
     * @param RequestStack
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    ) {
        parent::__construct($formFactory, $requestStack);

        $this->addDefaultSearchField();
    }

    protected function addDefaultSearchField(): self
    {
        $this->builder->add('search', CoreType\TextType::class, [
            'attr' => [
                'maxlength' => 90,
            ],
            'required' => false,
            'constraints' => [
                new Assert\Length([
                    'min' => 3,
                    'max' => 90,
                ]),
            ],
        ]);

        return $this;
    }
}

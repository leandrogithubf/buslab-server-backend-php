<?php

namespace App\Topnode\BaseBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class ApiFormTypeFactory
{
    /**
     * @var Symfony\Component\Form\FormBuilderInterface
     */
    protected $builder;

    /**
     * @var ?string
     */
    protected $dataClass = null;

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param FormFactoryInterface
     * @param RequestStack
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();

        $this->builder = $formFactory
            ->createNamedBuilder(
                null,
                FormType::class,
                $this->dataClass ? new $this->dataClass() : null,
                [
                    'csrf_protection' => false,
                    'allow_extra_fields' => true,
                ]
            )
        ;

        $this->builder->setMethod($this->method);

        $this->addCustomFields();
    }

    final public function getBuilder(): FormBuilder
    {
        return $this->builder;
    }

    final public function getForm(): Form
    {
        return $this->builder->getForm();
    }

    final public function getFormHandled(): Form
    {
        $form = $this->getForm();

        $form->handleRequest($this->request);
        if (!$form->isSubmitted()) {
            $data = json_decode($this->request->getContent(), true);
            if (null !== $data) {
                $form->submit(json_decode($this->request->getContent(), true), 'PATCH' != $this->method);
            }
        }

        return $form;
    }

    /**
     * @param mixed data (object or array) to be handled by the form
     *
     * @return ApiFormTypeFactory
     */
    final public function setData($data): self
    {
        $this->builder->setData($data);

        return $this;
    }

    /**
     * @param
     *
     * @return ApiFormTypeFactory
     */
    final public function setMethod(string $method): self
    {
        $this->builder->setMethod($method);

        return $this;
    }

    /**
     * @return ApiFormTypeFactory
     */
    protected function addCustomFields(): self
    {
        return $this;
    }
}

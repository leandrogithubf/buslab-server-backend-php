<?php

namespace App\Form\Api\Adm;

use App\Entity\Line;
use App\Entity\Vehicle;
use App\Topnode\BaseBundle\Form\ApiFormTypeFactory;
use App\Topnode\BaseBundle\Form\ApiSearchFormTypeFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReportFilesSearchTypeFactory extends ApiSearchFormTypeFactory
{
    public function addCustomFields(): ApiFormTypeFactory
    {
        $this->builder
            ->add('start', CoreType\DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd H:mm:ss',
            ])
            ->add('end', CoreType\DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('days', CoreType\TextType::class)
            ->add('sequence', CoreType\CollectionType::class, [
                'required' => false,
                'allow_add' => true,
                'entry_type' => CoreType\DateTimeType::class,
                'entry_options' => [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                ],
            ])
            ->add('vehicle', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Vehicle::class,
                'choice_value' => 'identifier',
            ])
            ->add('dateStart', CoreType\DateTimeType::class, [
                    'format' => 'dd/MM/yyyy H:mm',
                    'widget' => 'single_text',
            ])
            ->add('dateEnd', CoreType\DateTimeType::class, [
                    'format' => 'dd/MM/yyyy H:mm',
                    'widget' => 'single_text',
            ])
            ->add('line', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'class' => Line::class,
                'choice_value' => 'identifier',
            ])
            // ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            //     $data = $event->getData();

            //     if (isset($data['vehicle']) && isset($data['vehicle'][0]) && '' == $data['vehicle'][0]) {
            //         $data['vehicle'] = [];
            //         $event->setData($data);
            //     }

            //     if (isset($data['driver']) && isset($data['driver'][0]) && '' == $data['driver'][0]) {
            //         $data['driver'] = [];
            //         $event->setData($data);
            //     }

            //     if (isset($data['line']) && isset($data['line'][0]) && '' == $data['line'][0]) {
            //         $data['line'] = [];
            //         $event->setData($data);
            //     }
            // })
        ;

        return $this;
    }
}

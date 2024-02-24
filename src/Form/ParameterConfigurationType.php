<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Entity\ParameterConfiguration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParameterConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameter', EntityType::class, [
                'class' => Parameter::class,
                'choice_value' => 'identifier',
            ])
            ->add('minAllowed', CoreType\TextType::class)
            ->add('maxAllowed', CoreType\TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParameterConfiguration::class,
        ]);
    }
}

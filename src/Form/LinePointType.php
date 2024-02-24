<?php

namespace App\Form;

use App\Entity\LinePoint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinePointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', CoreType\TextType::class)
            ->add('latitude', CoreType\TextType::class)
            ->add('longitude', CoreType\TextType::class)
            ->add('sequence', CoreType\TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LinePoint::class,
        ]);
    }
}

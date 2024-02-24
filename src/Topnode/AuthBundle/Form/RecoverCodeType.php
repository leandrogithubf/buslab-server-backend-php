<?php

namespace App\Topnode\AuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * TODO: Document the class and methods
 * TODO: Check the validation values.
 */
class RecoverCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'app.security.recover_execute.form.fields.code',
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 15,
                        'max' => 15,
                    ]),
                ],
                'attr' => [
                    'maxlength' => 15,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}

<?php

namespace App\Topnode\AuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * TODO: Document the class and methods
 * TODO: Check the validation values.
 */
class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EmailType::class, [
                'label' => 'app.security.login.form.fields.user',
                'constraints' => [
                    new Email(),
                    new NotBlank(),
                    new Length([
                        'min' => 3,
                        'max' => 150,
                    ]),
                ],
                'attr' => [
                    'maxlength' => 150,
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'app.security.login.form.fields.password',
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'max' => 90,
                    ]),
                ],
                'attr' => [
                    'maxlength' => 90,
                ],
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'app.security.login.form.fields.remember_me',
                'constraints' => [
                    new Type([
                        'type' => 'boolean',
                    ]),
                ],
                'data' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}

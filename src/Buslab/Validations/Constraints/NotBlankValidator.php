<?php

namespace App\Buslab\Validations\Constraints;

/* use App\Topnode\BaseBundle\Utils\Dev\DevUtils; */
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\UserRepository;

class NotBlankValidator extends ConstraintValidator
{
    private $user;
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {   
        //Enviar Formulários com o Nome Vazio
        $metadata = $this->context->getMetadata();
        if($metadata->getPropertyName() === 'email' && !$value){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }

        //Enviar Formulários com Email Vazio
        if($metadata->getPropertyName() === 'name' && !$value){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
        }

        //Verifica se o usuário é Root ou System Admin
        $role = $this->context->getObject()->getRoleId();
        if($role->id == $_ENV['ROLE_ID_ROOT_DEV'] || $role->id == $_ENV['ROLE_ID_SYSTEM_DEV']){
            return;
        }

        if(!$value){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
        }
        
    }
}
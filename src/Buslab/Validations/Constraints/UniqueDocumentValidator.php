<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\UserRepository;

class UniqueDocumentValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {   
        //Verifica se o usuário é Root ou System Admin
        $role = $this->context->getObject()->getRoleId();
        if(!$value && $role->id != $_ENV['ROLE_ID_ROOT_DEV'] && $role->id != $_ENV['ROLE_ID_SYSTEM_DEV']){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }
        
        if(!$value && ($role->id == $_ENV['ROLE_ID_ROOT_DEV'] || $role->id == $_ENV['ROLE_ID_SYSTEM_DEV'])){
            return;
        } 

        $user = $this->context->getObject();
        $countDocument = $this->repository->documentExist($user->id, $value);
        
        if($countDocument > 0){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }
        
    }
}
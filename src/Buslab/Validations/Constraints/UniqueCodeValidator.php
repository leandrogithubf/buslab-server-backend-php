<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\EmployeeRepository;

class UniqueCodeValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {       
        if(!$value){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }        

        $employee = $this->context->getObject();
        $countCode = $this->repository->codeExist($employee->id, $employee->company->id, $value);
        
        if($countCode > 0){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }
        
    }
}
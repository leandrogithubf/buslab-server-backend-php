<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\EmployeeRepository;

class UniqueCNHValidator extends ConstraintValidator
{
    private $repository;

    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint)
    {           
        $modality = $this->context->getObject()->getModality();
        
        if(!$value && $modality->id != 2){
            //Não valida o formulário
            $this->context->buildViolation("Campo(s) obrigatório(s) não preenchido(s).")
            ->addViolation();
            return;
        }        

        $countCNH = 0;
        if($value){
            $employee = $this->context->getObject();
            $countCNH = $this->repository->cnhExist($employee->id, $employee->company->id, $value);
        }
                
        if($countCNH > 0){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
            return;
        }        
    }
}
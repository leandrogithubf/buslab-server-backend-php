<?php

namespace App\Buslab\Validations\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Repository\VehicleRepository;
use App\Repository\CompanyRepository;

class UniquePrefixValidator extends ConstraintValidator
{   
    private $repository;
    private $companyRepository;

    public function __construct(VehicleRepository $repository, CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
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

        $id = $this->context->getObject()->id;
        $company = $this->context->getObject()->getCompany();
        $countPrefix = $this->repository->countPrefixPerCity($value, $company->city->id, $id);
        
        if(intval($countPrefix) > 0){
            //Não valida o formulário
            $this->context->buildViolation($constraint->getMessage())
            ->addViolation();
        }                
    }
}
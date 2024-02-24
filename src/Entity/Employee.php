<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use App\Buslab\Validations\Constraints as BuslabValidations;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 */
class Employee extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @BuslabValidations\UniqueCode
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="employees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity=EmployeeModality::class, inversedBy="employees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modality;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @BuslabValidations\UniqueCNH
     */
    private $cnh;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $cnhExpiration;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $cellphone;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getModality(): ?EmployeeModality
    {
        return $this->modality;
    }

    public function setModality(?EmployeeModality $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getCnh(): ?string
    {
        return $this->cnh;
    }

    public function setCnh(?string $cnh): self
    {
        $this->cnh = $cnh;

        return $this;
    }

    public function getCnhExpiration(): ?\DateTimeInterface
    {
        return $this->cnhExpiration;
    }

    public function setCnhExpiration(?\DateTimeInterface $cnhExpiration): self
    {
        $this->cnhExpiration = $cnhExpiration;

        return $this;
    }

    public function getCellphone(): ?string
    {
        return $this->cellphone;
    }

    public function setCellphone(?string $cellphone): self
    {
        $this->cellphone = $cellphone;

        return $this;
    }
}

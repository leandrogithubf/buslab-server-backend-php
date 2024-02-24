<?php

namespace App\Entity;

use App\Repository\EmployeeModalityRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmployeeModalityRepository::class)
 */
class EmployeeModality extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\ShortDescriptionTrait;
    public const DRIVER = 1;
    public const COLLECTOR = 2;

    /**
     * @ORM\OneToMany(targetEntity=Employee::class, mappedBy="modality")
     */
    private $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    /**
     * @return Collection|Employee[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): self
    {
        if (!$this->employees->contains($employee)) {
            $this->employees[] = $employee;
            $employee->setModality($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): self
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            // set the owning side to null (unless already changed)
            if ($employee->getModality() === $this) {
                $employee->setModality(null);
            }
        }

        return $this;
    }
}

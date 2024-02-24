<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use App\Topnode\BaseBundle\Entity\MappedSuperclass\StreetAddress;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 */
class Company extends StreetAddress
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\OneToMany(targetEntity=Vehicle::class, mappedBy="company")
     */
    protected $vehicles;

    /**
     * @ORM\OneToMany(targetEntity=CompanyPlace::class, mappedBy="company")
     */
    protected $companyPlaces;

    /**
     * @ORM\OneToMany(targetEntity=Employee::class, mappedBy="company")
     */
    protected $employees;

    /**
     * @ORM\OneToMany(targetEntity=Line::class, mappedBy="company")
     */
    protected $lines;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class)
     */
    private $rolePlan;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->companyPlaces = new ArrayCollection();
        $this->employees = new ArrayCollection();
        $this->lines = new ArrayCollection();
    }

    /**
     * @return Collection|Vehicle[]
     */
    public function getVehicles(): Collection
    {
        return $this->vehicles;
    }

    public function addVehicle(Vehicle $vehicle): self
    {
        if (!$this->vehicles->contains($vehicle)) {
            $this->vehicles[] = $vehicle;
            $vehicle->setCompany($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicle $vehicle): self
    {
        if ($this->vehicles->contains($vehicle)) {
            $this->vehicles->removeElement($vehicle);
            // set the owning side to null (unless already changed)
            if ($vehicle->getCompany() === $this) {
                $vehicle->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CompanyPlace[]
     */
    public function getCompanyPlaces(): Collection
    {
        return $this->companyPlaces;
    }

    public function addCompanyPlace(CompanyPlace $companyPlace): self
    {
        if (!$this->companyPlaces->contains($companyPlace)) {
            $this->companyPlaces[] = $companyPlace;
            $companyPlace->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyPlace(CompanyPlace $companyPlace): self
    {
        if ($this->companyPlaces->contains($companyPlace)) {
            $this->companyPlaces->removeElement($companyPlace);
            // set the owning side to null (unless already changed)
            if ($companyPlace->getCompany() === $this) {
                $companyPlace->setCompany(null);
            }
        }

        return $this;
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
            $employee->setCompany($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): self
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            // set the owning side to null (unless already changed)
            if ($employee->getCompany() === $this) {
                $employee->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Line[]
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(Line $line): self
    {
        if (!$this->lines->contains($line)) {
            $this->lines[] = $line;
            $line->setCompany($this);
        }

        return $this;
    }

    public function removeLine(Line $line): self
    {
        if ($this->lines->contains($line)) {
            $this->lines->removeElement($line);
            // set the owning side to null (unless already changed)
            if ($line->getCompany() === $this) {
                $line->setCompany(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getRolePlan(): ?Role
    {
        return $this->rolePlan;
    }

    public function setRolePlan(?Role $rolePlan): self
    {
        $this->rolePlan = $rolePlan;

        return $this;
    }
}

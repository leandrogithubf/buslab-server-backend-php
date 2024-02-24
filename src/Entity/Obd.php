<?php

namespace App\Entity;

use App\Repository\ObdRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use App\Buslab\Validations\Constraints as BuslabValidation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ObdRepository::class)
 * @UniqueEntity(
 *     fields={"serial"},
 *     message="Este serial já está sendo usado."
 * )
 */
class Obd extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string", length=15)
     * @Assert\NotBlank(message="O serial não pode estar vazio.")
     */
    protected $serial;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="A versão não pode estar vazio.")
     */
    protected $version;

    /**
     * @ORM\ManyToOne(targetEntity=CellphoneNumber::class)
     */
    protected $cellphoneNumber;

    /**
     * @ORM\OneToMany(targetEntity=Checkpoint::class, mappedBy="obd")
     */
    protected $checkpoints;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     */
    protected $company;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    public function __construct()
    {
        $this->checkpoints = new ArrayCollection();
    }

    /**
     * @return Collection|Checkpoint[]
     */
    public function getCheckpoints(): Collection
    {
        return $this->checkpoints;
    }

    public function addCheckpoint(Checkpoint $checkpoint): self
    {
        if (!$this->checkpoints->contains($checkpoint)) {
            $this->checkpoints[] = $checkpoint;
            $checkpoint->setObd($this);
        }

        return $this;
    }

    public function removeCheckpoint(Checkpoint $checkpoint): self
    {
        if ($this->checkpoints->contains($checkpoint)) {
            $this->checkpoints->removeElement($checkpoint);
            // set the owning side to null (unless already changed)
            if ($checkpoint->getObd() === $this) {
                $checkpoint->setObd(null);
            }
        }

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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}

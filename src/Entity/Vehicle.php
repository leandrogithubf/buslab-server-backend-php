<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use App\Buslab\Validations\Constraints as BuslabValidation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VehicleRepository::class)
 * @UniqueEntity(
 *     fields={"plate"},
 *     message="Esta placa já está em uso"
 * )
 * @UniqueEntity(
 *     fields={"chassi"},
 *     message="Este chassis já está em uso"
 * )
 * @UniqueEntity(
 *     fields={"obd"},
 *     message="Este obd já está em uso"
 * )
 */
class Vehicle extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @BuslabValidation\UniquePrefix
     */
    protected $prefix;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $plate;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     */
    protected $consumptionTarget;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank
     */
    protected $startOperation;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="vehicles")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    protected $company;

    /**
     * @ORM\ManyToOne(targetEntity=VehicleModel::class, inversedBy="vehicles")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    protected $model;

    /**
     * @ORM\OneToOne(targetEntity=Obd::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $obd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $manufacture;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $chassi;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $manufactoreBodywork;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $doorsNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bodywork;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $seats;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $standing;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $periodicInspection;

    /**
     * @ORM\ManyToOne(targetEntity=VehicleStatus::class)
     */
    private $status;

    public function getLabel(): string
    {
        return $this->getPrefix() . ' - ' . $this->getPlate();
    }

    public function getSerialPrefix(): string
    {
        if ($this->getObd()) {
            return $this->getPrefix() . ' - ' . $this->getObd()->getSerial();
        }

        return $this->getPrefix();
    }

    public function getModel(): ?VehicleModel
    {
        return $this->model;
    }

    public function setModel(?VehicleModel $model): self
    {
        $this->model = $model;

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

    public function getObd(): ?Obd
    {
        return $this->obd;
    }

    public function setObd(?Obd $obd): self
    {
        $this->obd = $obd;

        return $this;
    }

    public function getBodywork(): ?string
    {
        return $this->bodywork;
    }

    public function setBodywork(?string $bodywork): self
    {
        $this->bodywork = $bodywork;

        return $this;
    }

    public function getConsumptionTarget(): ?string
    {
        return $this->consumptionTarget;
    }

    public function setConsumptionTarget(?string $consumptionTarget): self
    {
        $this->consumptionTarget = $consumptionTarget;

        return $this;
    }

    public function getManufactoreBodywork(): ?string
    {
        return $this->manufactoreBodywork;
    }

    public function setManufactoreBodywork(?string $manufactoreBodywork): self
    {
        $this->manufactoreBodywork = $manufactoreBodywork;

        return $this;
    }

    public function getDoorsNumber(): ?string
    {
        return $this->doorsNumber;
    }

    public function setDoorsNumber(?string $doorsNumber): self
    {
        $this->doorsNumber = $doorsNumber;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(?int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getStanding(): ?int
    {
        return $this->standing;
    }

    public function setStanding(?int $standing): self
    {
        $this->standing = $standing;

        return $this;
    }

    public function getPeriodicInspection(): ?\DateTimeInterface
    {
        return $this->periodicInspection;
    }

    public function setPeriodicInspection(?\DateTimeInterface $periodicInspection): self
    {
        $this->periodicInspection = $periodicInspection;

        return $this;
    }

    public function getStatus(): ?VehicleStatus
    {
        return $this->status;
    }

    public function setStatus(?VehicleStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\EventRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $action;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $end;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class)
     */
    protected $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=EventModality::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $modality;

    /**
     * @ORM\ManyToOne(targetEntity=EventStatus::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity=EventCategory::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity=Line::class)
     */
    protected $line;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    protected $employee;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $partial;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tableRef;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $garage;

    /**
     * @ORM\Column(type="string", length=14)
     */
    protected $protocol;

    /**
     * @ORM\ManyToOne(targetEntity=Trip::class, inversedBy="events")
     */
    private $trip;

    /**
     * @ORM\ManyToOne(targetEntity=Sector::class)
     */
    private $sector;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $local;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    private $collector;

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): self
    {
        $this->trip = $trip;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(?string $local): self
    {
        $this->local = $local;

        return $this;
    }

    public function getDriver(): ?Employee
    {
        return $this->driver;
    }

    public function setDriver(?Employee $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getCollector(): ?Employee
    {
        return $this->collector;
    }

    public function setCollector(?Employee $collector): self
    {
        $this->collector = $collector;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\TripRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TripRepository::class)
 */
class Trip extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    private $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    private $collector;

    /**
     * @ORM\ManyToOne(targetEntity=Line::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $line;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=Obd::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $obd;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     */
    private $company;

    /**
     * @ORM\Column(type="datetime")
     */
    private $starts_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $ends_at;

    /**
     * @ORM\ManyToOne(targetEntity=TripStatus::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=TripModality::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $modality;

    /**
     * @ORM\ManyToOne(targetEntity=Report::class, inversedBy="trip")
     */
    private $report;

    /**
     * @ORM\OneToMany(targetEntity=Checkpoint::class, mappedBy="trip")
     */
    private $checkpoints;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="trip")
     */
    private $events;

    /**
     * @ORM\OneToOne(targetEntity=ScheduleDate::class, inversedBy="trip", cascade={"persist", "remove"})
     */
    private $scheduleDate;

    public function __construct()
    {
        $this->checkpoints = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLine(): ?Line
    {
        return $this->line;
    }

    public function setLine(?Line $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->starts_at;
    }

    public function setStartsAt(\DateTimeInterface $starts_at): self
    {
        $this->starts_at = $starts_at;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->ends_at;
    }

    public function setEndsAt(?\DateTimeInterface $ends_at): self
    {
        $this->ends_at = $ends_at;

        return $this;
    }

    public function getStatus(): ?TripStatus
    {
        return $this->status;
    }

    public function setStatus(?TripStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getModality(): ?TripModality
    {
        return $this->modality;
    }

    public function setModality(?TripModality $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
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
            $checkpoint->setTrip($this);
        }

        return $this;
    }

    public function removeCheckpoint(Checkpoint $checkpoint): self
    {
        if ($this->checkpoints->contains($checkpoint)) {
            $this->checkpoints->removeElement($checkpoint);
            // set the owning side to null (unless already changed)
            if ($checkpoint->getTrip() === $this) {
                $checkpoint->setTrip(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setTrip($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getTrip() === $this) {
                $event->setTrip(null);
            }
        }

        return $this;
    }

    public function getScheduleDate(): ?ScheduleDate
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(?ScheduleDate $scheduleDate): self
    {
        $this->scheduleDate = $scheduleDate;

        return $this;
    }
}

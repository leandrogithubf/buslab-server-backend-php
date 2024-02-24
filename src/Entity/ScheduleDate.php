<?php

namespace App\Entity;

use App\Repository\ScheduleDateRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScheduleDateRepository::class)
 */
class ScheduleDate extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class)
     */
    protected $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    protected $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    protected $collector;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity=Schedule::class, inversedBy="dates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $schedule;

    /**
     * @ORM\OneToOne(targetEntity=Trip::class, mappedBy="scheduleDate", cascade={"persist", "remove"})
     */
    private $trip;

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getTrip(): ?Trip
    {
        return $this->trip;
    }

    public function setTrip(?Trip $trip): self
    {
        $this->trip = $trip;

        // set (or unset) the owning side of the relation if necessary
        $newSchedule = null === $trip ? null : $this;
        if ($trip->getScheduleDate() !== $newSchedule) {
            $trip->setScheduleDate($newSchedule);
        }

        return $this;
    }
}

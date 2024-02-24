<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ScheduleRepository::class)
 */
class Schedule extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;
    use BaseEntity\DescriptionTrait;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    protected $tableCode;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('TRIP', 'MOVEMENT', 'RESERVED', 'STARTING_OPERATION', 'CLOSING_OPERATION')")
     */
    protected $modality;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('WEEKDAY', 'SATURDAY', 'SUNDAY')")
     */
    protected $weekInterval;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     */
    protected $company;

    /**
     * @ORM\ManyToOne(targetEntity=Line::class)
     */
    protected $line;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    protected $driver;

    /**
     * @ORM\ManyToOne(targetEntity=Employee::class)
     */
    protected $collector;

    /**
     * @ORM\ManyToOne(targetEntity=Vehicle::class)
     */
    protected $vehicle;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    protected $startsAt;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    protected $endsAt;

    /**
     * @ORM\Column(type="date")
     */
    protected $dataValidity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sequence;

    /**
     * @ORM\OneToMany(targetEntity=ScheduleDate::class, mappedBy="schedule", orphanRemoval=true)
     */
    private $dates;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    public function emptyDates(): self
    {
        $this->dates = new ArrayCollection();

        return $this;
    }

    /**
     * @return Collection|ScheduleDate[]
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(ScheduleDate $date): self
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->setSchedule($this);
        }

        return $this;
    }

    public function removeDate(ScheduleDate $date): self
    {
        if ($this->dates->contains($date)) {
            $this->dates->removeElement($date);
            // set the owning side to null (unless already changed)
            if ($date->getSchedule() === $this) {
                $date->setSchedule(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\LineRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LineRepository::class)
 */
class Line extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\DescriptionTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string")
     */
    protected $code;

    /**
     * @ORM\Column(type="float")
     */
    protected $passage;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="routes")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $company;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('GOING', 'RETURN', 'CIRCULATE')")
     * @Assert\Choice({"GOING", "RETURN", "CIRCULATE"})
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected $direction;

    /**
     * @ORM\OneToMany(targetEntity=LinePoint::class, mappedBy="line", orphanRemoval=true)
     * @ORM\OrderBy({"sequence" = "ASC"})
     **/
    protected $points;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maxSpeed;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getLabel(): string
    {
        return $this->getCode() . ' - ' . $this->getDescription() . ' - ' . $this->getDirection($asHuman = true);
    }

    public function getDirection($asHuman = false)
    {
        if ($asHuman) {
            if ($this->direction === 'GOING') {
                return '(ida)';
            }

            if ($this->direction === 'RETURN') {
                return '(volta)';
            }

            if ($this->direction === 'CIRCULATE') {
                return '(circular)';
            }
        }

        return $this->direction;
    }

    /**
     * @return Collection|LinePoint[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(LinePoint $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setLine($this);
        }

        return $this;
    }

    public function removePoint(LinePoint $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getLine() === $this) {
                $point->setLine(null);
            }
        }

        return $this;
    }

    public function getMaxSpeed(): ?int
    {
        return $this->maxSpeed;
    }

    public function setMaxSpeed(?int $maxSpeed): self
    {
        $this->maxSpeed = $maxSpeed;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\EventCategoryRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventCategoryRepository::class)
 */
class EventCategory extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Parameter::class)
     */
    protected $parameter;

    /**
     * @ORM\ManyToOne(targetEntity=Sector::class)
     */
    private $sector;

    public function getParameter(): ?Parameter
    {
        return $this->parameter;
    }

    public function setParameter(?Parameter $parameter): self
    {
        $this->parameter = $parameter;

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
}

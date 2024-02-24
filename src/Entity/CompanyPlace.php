<?php

namespace App\Entity;

use App\Repository\CompanyPlaceRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompanyPlaceRepository::class)
 */
class CompanyPlace extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="companyPlaces")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $company;

    /**
     * @ORM\Column(type="text")
     */
    private $markers;

    public function getMarkers(): ?string
    {
        return $this->markers;
    }

    public function setMarkers(string $markers): self
    {
        $this->markers = $markers;

        return $this;
    }
}

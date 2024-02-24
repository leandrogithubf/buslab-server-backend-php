<?php

namespace App\Entity;

use App\Repository\VehicleStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Topnode\BaseBundle\Entity as BaseEntity;

/**
 * @ORM\Entity(repositoryClass=VehicleStatusRepository::class)
 */
class VehicleStatus extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}

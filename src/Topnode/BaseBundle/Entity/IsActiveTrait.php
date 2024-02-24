<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This trait has the base and default id property and methods for entities.
 */
trait IsActiveTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}

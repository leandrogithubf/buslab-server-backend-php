<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This trait has the base and default timestamps properties and methods for
 * entities.
 */
trait ExpirableTrait
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isUsed;

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getIsExpired(): ?bool
    {
        return $this->getExpiresAt() < new \DateTime();
    }

    public function getIsUsed(): ?bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): self
    {
        $this->isUsed = $isUsed;

        return $this;
    }
}

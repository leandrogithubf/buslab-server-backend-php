<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This trait has the base and default timestamps properties and methods for
 * entities.
 */
trait TimestampsTrait
{
    use TimestampsSimpleTrait;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}

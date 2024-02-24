<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This trait has the username propertie and methods for entities.
 */
trait UsernameTrait
{
    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $username;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
}

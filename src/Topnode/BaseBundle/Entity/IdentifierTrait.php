<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the base and default timestamps properties and methods for
 * entities.
 */
trait IdentifierTrait
{
    /**
     * @ORM\Column(type="string", length=15)
     * @Assert\Length(
     *      min = 15,
     *      max = 15
     * )
     * @Assert\Regex("/^[a-zA-Z\d\-\_]{15}$/")
     */
    protected $identifier;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}

<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the ip propertie and methods for entities.
 */
trait IpTrait
{
    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 45
     * )
     * @Assert\Ip(
     *      version = Assert\Ip::ALL
     * )
     */
    protected $ip;

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}

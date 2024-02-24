<?php

namespace App\Entity;

use App\Repository\ParameterValueRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParameterValueRepository::class)
 */
class ParameterValue
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Obd::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $obd;

    /**
     * @ORM\ManyToOne(targetEntity=Parameter::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $parameter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    public function getObd(): ?Obd
    {
        return $this->obd;
    }

    public function setObd(?Obd $obd): self
    {
        $this->obd = $obd;

        return $this;
    }

    public function getParameter(): ?Parameter
    {
        return $this->parameter;
    }

    public function setParameter(?Parameter $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}

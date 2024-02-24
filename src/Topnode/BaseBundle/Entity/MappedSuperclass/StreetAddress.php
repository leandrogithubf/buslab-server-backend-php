<?php

namespace App\Topnode\BaseBundle\Entity\MappedSuperclass;

use App\Topnode\BaseBundle\Entity\AbstractBaseEntity;
use App\Topnode\BaseBundle\Entity\CityInterface;
use App\Topnode\BaseBundle\Entity\StateInterface;
use App\Topnode\BaseBundle\Entity\StreetAddressInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class StreetAddress extends AbstractBaseEntity implements StreetAddressInterface
{
    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $streetCode;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $streetName;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $streetNumber;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $streetComplement;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $streetDistrict;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $city;

    public function getCity(): ?CityInterface
    {
        return $this->city;
    }

    public function setCity(CityInterface $city): StreetAddressInterface
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?StateInterface
    {
        return $this->city->getState();
    }

    public function getFullAddress(): string
    {
        $addressLine = $this->streetName;
        if ($this->streetNumber && strlen($this->streetNumber) > 0) {
            $addressLine .= ', ' . $this->streetNumber;
        }

        if ($this->streetComplement && strlen($this->streetComplement) > 0) {
            $addressLine .= ', ' . $this->streetComplement;
        }

        if ($this->streetDistrict && strlen($this->streetDistrict) > 0) {
            $addressLine .= ' - ' . $this->streetDistrict;
        }

        if ($this->streetCode && strlen($this->streetCode) > 0) {
            $addressLine .= ' - ' . $this->streetCode;
        }

        $addressLine .= ' - ' . $this->getCity()->getName();
        $addressLine .= ' - ' . $this->getState()->getName();

        return $addressLine;
    }
}

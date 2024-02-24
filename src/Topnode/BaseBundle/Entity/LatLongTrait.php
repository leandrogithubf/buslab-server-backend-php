<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the latitude and longitude propertie and methods for entities.
 */
trait LatLongTrait
{
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type("float")
     * @Assert\Length(
     *      min = 1,
     *      max = 12
     * )
     * @Assert\Range(
     *      min = -90,
     *      max = 90
     * )
     */
    protected $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type("float")
     * @Assert\Length(
     *      min = 1,
     *      max = 12
     * )
     * @Assert\Range(
     *      min = -180,
     *      max = 180
     * )
     */
    protected $longitude;

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}

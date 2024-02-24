<?php

namespace App\Topnode\BaseBundle\Entity\MappedSuperclass;

use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class City extends BaseEntity\AbstractBaseEntity implements BaseEntity\CityInterface
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $state;

    public function getCityAndStateDescription(): string
    {
        return $this->getName() . ' / ' . $this->getState()->getInitials();
    }

    /**
     * Set state.
     *
     * @return City
     */
    public function setState(BaseEntity\StateInterface $state): BaseEntity\CityInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return BaseEntity\StateInterface
     */
    public function getState(): ?BaseEntity\StateInterface
    {
        return $this->state;
    }
}

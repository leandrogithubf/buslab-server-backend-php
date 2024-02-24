<?php

namespace App\Entity;

use App\Repository\ParameterConfigurationRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParameterConfigurationRepository::class)
 */
class ParameterConfiguration extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\TimestampsTrait;
    use BaseEntity\IsActiveTrait;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $maxAllowed;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $minAllowed;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $company;

    /**
     * @ORM\ManyToOne(targetEntity=Parameter::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $parameter;
}

<?php

namespace App\Topnode\BaseBundle\Entity\MappedSuperclass;

use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class State extends BaseEntity\AbstractBaseEntity implements BaseEntity\StateInterface
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $initials;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $name;
}

<?php

namespace App\Topnode\AuthBundle\Entity\MappedSuperclass;

use App\Topnode\AuthBundle\Entity\RoleInterface;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Role extends BaseEntity\AbstractBaseEntity implements RoleInterface
{
    use BaseEntity\IdTrait;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $role;

    /**
     * @ORM\Column(type="string", length=90)
     */
    protected $description;
}

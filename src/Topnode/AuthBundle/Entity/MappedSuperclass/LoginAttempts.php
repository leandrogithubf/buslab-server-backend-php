<?php

namespace App\Topnode\AuthBundle\Entity\MappedSuperclass;

use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class LoginAttempts extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\TimestampsSimpleTrait;
    use BaseEntity\IpTrait;
    use BaseEntity\UsernameTrait;
}

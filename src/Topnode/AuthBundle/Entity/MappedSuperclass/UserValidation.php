<?php

namespace App\Topnode\AuthBundle\Entity\MappedSuperclass;

use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_identifier", columns={"identifier"})
 * })
 */
abstract class UserValidation extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\TimestampsSimpleTrait;

    public const EXPIRE_TIME_SECONDS = 3600;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $isUsed = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;
}

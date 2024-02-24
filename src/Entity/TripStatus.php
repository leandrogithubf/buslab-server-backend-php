<?php

namespace App\Entity;

use App\Repository\TripStatusRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TripStatusRepository::class)
 */
class TripStatus extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;

    public const SCHEDULED = 1;
    public const STARTED = 2;
    public const DONE = 3;
    public const NON_PRODUCTIVE = 4;
}

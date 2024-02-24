<?php

namespace App\Entity;

use App\Repository\TripModalityRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TripModalityRepository::class)
 */
class TripModality extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;

    public const SCHEDULED = 1;
    public const UNSCHEDULED = 2;
}

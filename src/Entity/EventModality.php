<?php

namespace App\Entity;

use App\Repository\EventModalityRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventModalityRepository::class)
 */
class EventModality extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;

    public const OCCURRENCE = 1;
    public const EVENT = 2;
}

<?php

namespace App\Entity;

use App\Repository\ReportTypeRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportTypeRepository::class)
 */
class ReportType extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\DescriptionTrait;
}

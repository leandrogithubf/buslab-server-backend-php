<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the short description propertie and methods for entities.
 */
trait ShortDescriptionTrait
{
    use DescriptionTrait;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 60,
     *      normalizer = {"\App\Topnode\BaseBundle\Entity\DescriptionTrait", "descriptionTraitNormalizer"}
     * )
     */
    protected $description;
}

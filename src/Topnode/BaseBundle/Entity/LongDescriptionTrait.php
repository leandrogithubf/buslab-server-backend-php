<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the long description propertie and methods for entities.
 */
trait LongDescriptionTrait
{
    use DescriptionTrait;

    /**
     * @ORM\Column(type="text")
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 300,
     *      normalizer = {"\App\Topnode\BaseBundle\Entity\DescriptionTrait", "descriptionTraitNormalizer"}
     * )
     */
    protected $description;
}

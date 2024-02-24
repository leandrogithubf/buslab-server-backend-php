<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the short description propertie and methods for entities.
 */
trait DescriptionTrait
{
    /**
     * @ORM\Column(type="string", length=120)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 120,
     *      normalizer = {"\App\Topnode\BaseBundle\Entity\DescriptionTrait", "descriptionTraitNormalizer"}
     * )
     */
    protected $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $this->descriptionTraitNormalizer($description);

        return $this;
    }

    public function descriptionTraitNormalizer($string)
    {
        return \App\Topnode\BaseBundle\Utils\String\StringUtils::normalize($string);
    }
}

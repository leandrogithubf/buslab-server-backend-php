<?php

namespace App\Entity;

use App\Repository\CellphoneNumberRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use App\Topnode\BaseBundle\Validator\Constraints as TopnodeAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CellphoneNumberRepository::class)
 * @UniqueEntity(
 *     fields={"number"},
 *     message="Este número já está sendo usado."
 * )
 */
class CellphoneNumber extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string", length=11)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=11,
     *     max=11
     * )
     * @TopnodeAssert\PhoneNumber
     */
    private $number;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}

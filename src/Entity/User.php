<?php

namespace App\Entity;

use App\Topnode\AuthBundle\Entity\MappedSuperclass\User as UserSupperClass;
use App\Topnode\BaseBundle\Validator\Constraints as TopNodeAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\UserRepository;
use App\Buslab\Validations\Constraints as BuslabValidation;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Este email já está em uso"
 * )
 */
class User extends UserSupperClass
{
    /**
     * @ORM\Column(type="string", length=255)
     * @BuslabValidation\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(
     *     min=3,
     *     max=255
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @BuslabValidation\NotBlank
     * @Assert\Length(
     *     min=11,
     *     max=11
     * )
     * @TopNodeAssert\PhoneNumber
     */
    protected $cellphone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @BuslabValidation\NotBlank
     * @Assert\Email
     * @Assert\Length(
     *     min=3,
     *     max=255
     * )
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @BuslabValidation\NotBlank
     * @BuslabValidation\UniqueDocument
     * @Assert\Length(
     *     min=11,
     *     max=11
     * )
     * @TopNodeAssert\Cpf
     */
    protected $documentNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class)
     * @BuslabValidation\NotBlank
     */
    private $company;

    /**
     * @ORM\ManyToMany(targetEntity=Line::class)
     */
    private $profileLines;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isNotificationEnabled = true;

    /**
     * @ORM\ManyToMany(targetEntity=EventCategory::class)
     */
    private $profileEventCategories;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class)
     */
    private $rolePlan;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class)
     */
    protected $role;


    public function __construct()
    {
        $this->profileLines = new ArrayCollection();
        $this->profileEventCategories = new ArrayCollection();
    }

    public function getUsername(): ?string
    {
        return $this->documentNumber ?? $this->cellphone ?? $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|Line[]
     */
    public function getProfileLines(): Collection
    {
        return $this->profileLines;
    }

    public function addProfileLine(Line $profileLine): self
    {
        if (!$this->profileLines->contains($profileLine)) {
            $this->profileLines[] = $profileLine;
        }

        return $this;
    }

    public function removeProfileLine(Line $profileLine): self
    {
        if ($this->profileLines->contains($profileLine)) {
            $this->profileLines->removeElement($profileLine);
        }

        return $this;
    }

    public function getIsNotificationEnabled(): ?bool
    {
        return $this->isNotificationEnabled;
    }

    public function setIsNotificationEnabled(bool $isNotificationEnabled): self
    {
        $this->isNotificationEnabled = $isNotificationEnabled;

        return $this;
    }

    /**
     * @return Collection|EventCategory[]
     */
    public function getProfileEventCategories(): Collection
    {
        return $this->profileEventCategories;
    }

    public function addProfileEventCategory(EventCategory $profileEventCategory): self
    {
        if (!$this->profileEventCategories->contains($profileEventCategory)) {
            $this->profileEventCategories[] = $profileEventCategory;
        }

        return $this;
    }

    public function removeProfileEventCategory(EventCategory $profileEventCategory): self
    {
        if ($this->profileEventCategories->contains($profileEventCategory)) {
            $this->profileEventCategories->removeElement($profileEventCategory);
        }

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->getRole()->isCompanyRelated() && $this->getCompany() === null) {
            $context->buildViolation('O usuário deve ser vinculado a uma empresa')
                ->atPath('company')
                ->addViolation()
            ;
        }
    }

    public function getRolePlan(): ?Role
    {
        return $this->rolePlan;
    }

    public function setRolePlan(?Role $rolePlan): self
    {
        $this->rolePlan = $rolePlan;

        return $this;
    }

    public function getRoleId(): ?Role
    {
        return $this->role;
    }

    
}

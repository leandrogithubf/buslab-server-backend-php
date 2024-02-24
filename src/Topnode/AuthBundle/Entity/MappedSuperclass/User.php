<?php

namespace App\Topnode\AuthBundle\Entity\MappedSuperclass;

use App\Topnode\AuthBundle\Entity\RoleInterface;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_identifier", columns={"identifier"})
 * })
 */
abstract class User extends BaseEntity\AbstractBaseEntity implements UserInterface
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Topnode\AuthBundle\Entity\MappedSuperclass\Role", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $role;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * Used to define if a password has been encoded or not on register and
     * update steps.
     *
     * @var bool
     */
    private $isEncoded;

    // Permitindo descobrir se uma senha teve o encode feito ou não
    public function isEncoded(): ?bool
    {
        return $this->isEncoded;
    }

    public function getIsEncoded(): ?bool
    {
        return $this->isEncoded;
    }

    public function setIsEncoded(?bool $isEncoded): self
    {
        $this->isEncoded = $isEncoded;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): ?array
    {
        if ($this->role instanceof RoleInterface) {
            return [$this->role->getRole()];
        }

        return [];
    }

    /**
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        throw new \Exception('You must override this method with your own logic.');
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }
}

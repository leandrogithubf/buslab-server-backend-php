<?php

namespace App\Entity;

use App\Topnode\AuthBundle\Entity\MappedSuperclass\Role as RoleSupperClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 */
class Role extends RoleSupperClass
{
    public const ROLE_ROOT = 1;
    public const ROLE_SYSTEM_ADMIN = 2;
    public const ROLE_COMPANY_ADMIN = 3;
    public const ROLE_COMPANY_MANAGER = 4;
    public const ROLE_COMPANY_OPERATOR = 5;

    public function getRole(): string
    {
        return $this->role;
    }

    public function isCompanyRelated(): string
    {
        return in_array($this->id, [
            self::ROLE_COMPANY_ADMIN,
            self::ROLE_COMPANY_MANAGER,
            self::ROLE_COMPANY_OPERATOR,
        ]);
    }
}

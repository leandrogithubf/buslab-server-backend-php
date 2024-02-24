<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Topnode\BaseBundle\Migrations\Optional\RoleMigration;

final class Version20200503161110 extends RoleMigration
{
    protected $descriptions = [
        'ROLE_ROOT' => 'root',
        'ROLE_SYSTEM_ADMIN' => 'Administrador de Sistema',
        'ROLE_COMPANY_ADMIN' => 'Administrador de Empresa',
        'ROLE_COMPANY_MANAGER' => 'Gerente',
        'ROLE_COMPANY_OPERATOR' => 'Operador',
    ];
}

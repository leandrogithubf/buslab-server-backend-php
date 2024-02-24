<?php

namespace App\Topnode\BaseBundle\Migrations\Optional;

use App\Entity\Role;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class RoleMigration extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $tableName = 'role';
    protected $roleClass = Role::class;
    protected $descriptions = [];

    public function getDescription(): string
    {
        return 'Creates roles based on Role entity constants.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $em = $this->container->get('doctrine.orm.entity_manager');

        $reflectionRoleClass = new \ReflectionClass($this->roleClass);
        foreach ($reflectionRoleClass->getConstants() as $constant => $value) {
            if (0 !== strpos($constant, 'ROLE_')) {
                continue;
            }

            $roleFromDatabase = $em->getRepository($this->roleClass)
                  ->find($value)
            ;

            if ($roleFromDatabase) {
                continue;
            }

            if (array_key_exists($constant, $this->descriptions)) {
                $description = $this->descriptions[$constant];
            } else {
                $description = ucwords(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $constant)));
            }

            $this->addSql('INSERT INTO ' . $this->tableName . ' (id, role, description) VALUES (' . $value . ', "' . $constant . '", "' . $description . '");');
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM ' . $this->tableName . ';');
    }
}

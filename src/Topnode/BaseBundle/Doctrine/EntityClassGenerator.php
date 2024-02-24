<?php

namespace App\Topnode\BaseBundle\Doctrine;

use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @internal
 */
final class EntityClassGenerator
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateEntityClass(
        ClassNameDetails $entityClassDetails,
        bool $apiResource,
        bool $crudAtributes,
        bool $identifier
    ): string {
        $repoClassDetails = $this->generator->createClassNameDetails(
            $entityClassDetails->getRelativeName(),
            'Repository\\',
            'Repository'
        );

        $entityPath = $this->generator->generateClass(
            $entityClassDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/Entity.tpl.php',
            [
                'repository_full_class_name' => $repoClassDetails->getFullName(),
                'api_resource' => $apiResource,
                'crud_fields' => $crudAtributes,
                'identifier' => $identifier,
            ]
        );

        $entityAlias = strtolower($entityClassDetails->getShortName()[0]);
        $this->generator->generateClass(
            $repoClassDetails->getFullName(),
            'doctrine/Repository.tpl.php',
            [
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_alias' => $entityAlias,
                'with_password_upgrade' => false,
            ]
        );

        return $entityPath;
    }
}

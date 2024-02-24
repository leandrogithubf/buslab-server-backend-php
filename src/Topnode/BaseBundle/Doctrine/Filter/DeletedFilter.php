<?php

namespace App\Topnode\BaseBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class DeletedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // if (!$this->config['auto_filter_inactive']) {
        //     return;
        // }

        if ($targetEntity->hasField('isActive')) {
            return $targetTableAlias . '.is_active = 1';
        }

        return '';
    }
}

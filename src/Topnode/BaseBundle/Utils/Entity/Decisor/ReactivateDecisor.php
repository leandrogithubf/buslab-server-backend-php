<?php

namespace App\Topnode\BaseBundle\Utils\Entity\Decisor;

class ReactivateDecisor
{
    public static function isAllowed($object)
    {
        if (property_exists($object, 'isActive') && property_exists($object, 'deletedAt')) {
            if ($object->getIsActive()) {
                return true;
            }

            $now = new \DateTime();

            if (intval($object->getDeletedAt()->diff($now)->format('%R%a')) <= 1) {
                return true;
            }
        }

        return false;
    }
}

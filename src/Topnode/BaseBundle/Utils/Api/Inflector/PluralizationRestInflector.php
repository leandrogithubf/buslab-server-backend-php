<?php

namespace App\Topnode\BaseBundle\Utils\Api\Inflector;

use FOS\RestBundle\Inflector\InflectorInterface;

class PluralizationRestInflector implements InflectorInterface
{
    public function pluralize($word)
    {
        return $word; // Don't pluralize
    }
}

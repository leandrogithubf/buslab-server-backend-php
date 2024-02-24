<?php

namespace App\Entity;

use App\Repository\CityRepository;
use App\Topnode\BaseBundle\Entity\MappedSuperclass\City as CitySupperClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 */
class City extends CitySupperClass
{
}

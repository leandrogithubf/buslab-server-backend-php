<?php

namespace App\Entity;

use App\Repository\StateRepository;
use App\Topnode\BaseBundle\Entity\MappedSuperclass\State as StateSupperClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StateRepository::class)
 */
class State extends StateSupperClass
{
}

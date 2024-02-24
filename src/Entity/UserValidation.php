<?php

namespace App\Entity;

use App\Topnode\AuthBundle\Entity\MappedSuperclass\UserValidation as UserValidationSupperClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserValidationRepository")
 */
class UserValidation extends UserValidationSupperClass
{
}

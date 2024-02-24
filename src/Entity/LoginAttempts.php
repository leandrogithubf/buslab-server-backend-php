<?php

namespace App\Entity;

use App\Topnode\AuthBundle\Entity\MappedSuperclass\LoginAttempts as LoginAttemptsSupperClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LoginAttemptsRepository")
 */
class LoginAttempts extends LoginAttemptsSupperClass
{
}

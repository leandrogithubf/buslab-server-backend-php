<?php

namespace App\Controller;

use App\Topnode\AuthBundle\Controller\SecurityController as Login;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="security_")
 */
class SecurityController extends Login
{
    // Rewrite topnode security functions here
}

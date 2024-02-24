<?php

namespace App\Controller;

use App\Topnode\BaseBundle\Controller\FrontController as OriginalFrontController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="front_")
 */
class FrontController extends OriginalFrontController
{
    // Rewrite frontend functions here
}

<?php

namespace App\Topnode\BaseBundle\Controller;

use App\Topnode\BaseBundle\Utils\Configurator\Environment;
use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="front_")
 */
class FrontController extends AbstractController
{
    /**
     * @Route("/browserconfig.xml", name="browserconfig")
     */
    public function browserconfigAction(Environment $environment)
    {
        if (!$environment->get('front')['manage_front_files']) {
            return $this->get('service_container')
                ->get('tn.utils.api.response')
                ->error(404, 'file not found')
            ;
        }

        return $this->render('@TopnodeBase/front/browserconfig.xml.twig');
    }

    /**
     * @Route("/manifest.json", name="manifest")
     */
    public function manifestAction(Environment $environment)
    {
        if (!$environment->get('front')['manage_front_files']) {
            return $this->get('service_container')
                ->get('tn.utils.api.response')
                ->error(404, 'file not found')
            ;
        }

        return $this->render('@TopnodeBase/front/manifest.json.twig', [], new JsonResponse());
    }

    /**
     * @Route("/favicon.ico", name="favicon")
     */
    public function faviconAction(
        KernelInterface $kernel,
        Packages $assetPackage,
        FileHandler $fileHandler,
        Environment $environment
    ) {
        if (!$environment->get('front')['manage_front_files']) {
            return $this->get('service_container')
                ->get('tn.utils.api.response')
                ->error(404, 'file not found')
            ;
        }

        $path = $kernel->getProjectDir() . '/public/' . $assetPackage->getUrl('build/images/favicon.ico');

        return $fileHandler->view($path, 'favicon.ico');
    }
}

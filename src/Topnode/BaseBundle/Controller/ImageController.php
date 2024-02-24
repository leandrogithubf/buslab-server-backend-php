<?php

namespace App\Topnode\BaseBundle\Controller;

use App\Entity\ImageQueue;
use App\Topnode\BaseBundle\Form\ImageUploadType;
use App\Topnode\BaseBundle\Utils\Api\Response\Response as TnResponse;
use App\Topnode\BaseBundle\Utils\Multimedia\FileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/image", name="image_")
 */
abstract class ImageController extends AbstractController
{
    /**
     * @Route(
     *     "/upload",
     *     requirements={
     *         "_format"="json"
     *     },
     *     name="upload",
     *     format="json",
     *     methods={"POST"}
     * )
     */
    public function upload(
        FileHandler $fileHandler,
        TnResponse $response,
        Request $request
    ): JsonResponse {
        $form = $this->createForm(ImageUploadType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $response->error(400, 'Necessário enviar dados no formulário.'); // TODO: translate this message
        }

        if (!$form->isValid()) {
            return $response->errorFromForm($form->getErrors(true), 'Envio de dados do formulário é inválido.'); // TODO: translate this message
        }

        $entity = (new ImageQueue())->setTempImageFile($form->get('file')->getData());

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();

        return $response->response(200, [
            'identifier' => $entity->getImageIdentifier(),
            'temp_url' => $this->generateUrl('image_show', [
                'classname' => 'temp',
                'identifier' => $entity->getImageIdentifier(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * @Route(
     *  "/{classname}/{identifier}",
     *  name="show",
     *  methods={"GET"},
     *  requirements={"identifier"="^[0-9A-Za-z\-\_]{15}$"}
     *)
     */
    public function showAction(
        FileHandler $fileHandler,
        TnResponse $response,
        Request $request,
        string $classname,
        string $identifier
    ): Response {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('App:' . $this->translateUriToClassname($classname))
            ->findOneByImageIdentifier($identifier)
        ;

        if (null === $entity) {
            return $response->error(404, 'image not found'); // TODO: translate this message
        }

        if ($entity instanceof ImageQueue && $entity->getIsMigrated()) {
            return $this->redirectToRoute('image_show', [
                'classname' => $entity->getMigratedTo(),
                'identifier' => $entity->getImageIdentifier(),
            ], 301);
        }

        return $fileHandler->view($entity->getImagePath(), $identifier);
    }

    protected function translateUriToClassname(string $classname): string
    {
        return Str::asClassName($classname);
    }

    protected function translateClassnameToUri(string $classname): string
    {
        $matches = [];
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $classname, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('-', $ret);
    }
}

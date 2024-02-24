<?php

namespace App\Topnode\BaseBundle\Utils\Multimedia;

use App\Topnode\BaseBundle\Utils\String\Identifier;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Entity\ReportFiles;

class FileHandler
{
    private $fs;
    private $dir;

    public function __construct(KernelInterface $kernel)
    {
        $this->dir = $kernel->getUploadDir();
        $this->fs = new Filesystem();
    }

    public function upload($file, ?string $identifier = null)
    {
        $identifier = strlen($identifier) > 0 ? $identifier : Identifier::filename();

        return $file->move($this->ensureDirectoryStructure([
            md5(sha1(date('Y'))),
            md5(sha1(date('m'))),
            md5(sha1(date('d'))),
            substr($identifier, 0, 1),
            substr($identifier, 1, 1),
            substr($identifier, 2, 1),
            substr($identifier, 3, 1),
            substr($identifier, 4, 1),
        ]), $identifier . '.' . $file->guessExtension());
    }

    public function download($completePath, $downloadName, $type = 'download')
    {
        if (!file_exists($completePath)) {
            throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException('Arquivo não encontrado');
        }

        $downloadName .= '.' . pathinfo($completePath, PATHINFO_EXTENSION);

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($completePath));
        $response->headers->set('Content-length', filesize($completePath));

        if ('download' === $type) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $downloadName . '";');
        }

        $response->sendHeaders();

        $response->setContent($this->content($completePath));

        return $response;
    }

    public function view($completePath, $downloadName)
    {
        return $this->download($completePath, $downloadName, 'view');
    }

    public function content($completePath)
    {
        return readfile($completePath);
    }

    private function ensureDirectoryStructure(array $childs): string
    {
        $dir = $this->dir;
        foreach ($childs as $child) {
            if (empty($child)) {
                continue;
            }

            $dir .= '/' . $child;

            if (!$this->fs->exists($this->dir)) {
                $this->fs->mkdir($this->dir, 0775);
            }
        }

        return $dir;
    }

    public function recordFileBD($url)
    {
        $name = explode('/', $url);
        $entity = new ReportFiles();
        $entity
            ->setPath($url)
            ->setName($name[3])
            ->setSize(filesize($url))
            ;

        $description = explode('-', $name[3]);
        $description = str_replace('_', ' ', ucfirst($description[0]));

        $entity->setDescription($description);

        return $entity;
    }
}

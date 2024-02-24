<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * This trait has the image properties and methods for entities.
 */
trait ImageTrait
{
    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    protected $imageIdentifier;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $imagePath;

    /**
     * The path to the table entity template.
     *
     * @var UploadedFile
     */
    protected $tempImageFile;
}

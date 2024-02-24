<?php

namespace App\Topnode\FileBundle\Entity;

use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Topnode\FileBundle\Repository\FileRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_identifier", columns={"identifier"})
 * })
 */
class File
{
    use BaseEntity\IdTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDeleted;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    public function __construct()
    {
        $this->mails = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getOriginalName();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setSize(filesize($this->getPath()));
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }
}

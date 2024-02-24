<?php

namespace App\Topnode\BaseBundle\Utils\Event;

use Symfony\Component\EventDispatcher\Event;

class ImageEvent extends Event
{
    protected $object;

    protected $em;

    public function __construct($object, $em)
    {
        $this->object = $object;
        $this->em = $em;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function setEntityManager($em): self
    {
        $this->em = $em;

        return $this;
    }
}

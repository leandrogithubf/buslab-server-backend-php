<?php

namespace App\Topnode\BaseBundle\Entity;

interface CityInterface
{
    public function getCityAndStateDescription(): string;

    public function setState(StateInterface $state): CityInterface;

    public function getState(): ?StateInterface;
}

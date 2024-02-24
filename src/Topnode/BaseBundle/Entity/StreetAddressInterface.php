<?php

namespace App\Topnode\BaseBundle\Entity;

interface StreetAddressInterface
{
    public function getCity(): ?CityInterface;

    public function setCity(CityInterface $city): StreetAddressInterface;

    public function getState(): ?StateInterface;

    public function getFullAddress(): string;
}

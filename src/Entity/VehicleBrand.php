<?php

namespace App\Entity;

use App\Repository\VehicleBrandRepository;
use App\Topnode\BaseBundle\Entity as BaseEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VehicleBrandRepository::class)
 */
class VehicleBrand extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait;
    use BaseEntity\DescriptionTrait;
    use BaseEntity\IsActiveTrait;
    use BaseEntity\IdentifierTrait;
    use BaseEntity\TimestampsTrait;

    /**
     * @ORM\Column(type="string", length=120)
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 120,
     *      normalizer = {"\App\Topnode\BaseBundle\Entity\DescriptionTrait", "descriptionTraitNormalizer"}
     * )
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity=VehicleModel::class, mappedBy="brand")
     */
    private $vehicleModels;

    public function __construct()
    {
        $this->vehicleModels = new ArrayCollection();
    }

    /**
     * @return Collection|VehicleModel[]
     */
    public function getVehicleModels(): Collection
    {
        return $this->vehicleModels;
    }

    public function addVehicleModel(VehicleModel $vehicleModel): self
    {
        if (!$this->vehicleModels->contains($vehicleModel)) {
            $this->vehicleModels[] = $vehicleModel;
            $vehicleModel->setBrand($this);
        }

        return $this;
    }

    public function removeVehicleModel(VehicleModel $vehicleModel): self
    {
        if ($this->vehicleModels->contains($vehicleModel)) {
            $this->vehicleModels->removeElement($vehicleModel);
            // set the owning side to null (unless already changed)
            if ($vehicleModel->getBrand() === $this) {
                $vehicleModel->setBrand(null);
            }
        }

        return $this;
    }
}

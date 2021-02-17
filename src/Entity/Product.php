<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $main_unit;

    /**
     * @ORM\Column(type="float")
     */
    private $main_price;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $second_unit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $second_price;

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="products")
     */
    private $city;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $city_id;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="products")
     */
    private $shop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $shop_id;

    /**
     * @ORM\OneToMany(targetEntity=Device::class, mappedBy="product")
     */
    private $devices;

    public function __construct()
    {
        $this->devices = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMainUnit(): ?string
    {
        return $this->main_unit;
    }

    public function setMainUnit(string $main_unit): self
    {
        $this->main_unit = $main_unit;

        return $this;
    }

    public function getMainPrice(): ?float
    {
        return $this->main_price;
    }

    public function setMainPrice(float $main_price): self
    {
        $this->main_price = $main_price;

        return $this;
    }

    public function getSecondUnit(): ?string
    {
        return $this->second_unit;
    }

    public function setSecondUnit(?string $second_unit): self
    {
        $this->second_unit = $second_unit;

        return $this;
    }

    public function getSecondPrice(): ?float
    {
        return $this->second_price;
    }

    public function setSecondPrice(?float $second_price): self
    {
        $this->second_price = $second_price;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return Collection|Device[]
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(Device $device): self
    {
        if (!$this->devices->contains($device)) {
            $this->devices[] = $device;
            $device->setProduct($this);
        }

        return $this;
    }

    public function removeDevice(Device $device): self
    {
        if ($this->devices->removeElement($device)) {
            // set the owning side to null (unless already changed)
            if ($device->getProduct() === $this) {
                $device->setProduct(null);
            }
        }

        return $this;
    }
}

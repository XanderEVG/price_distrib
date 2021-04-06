<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeviceRepository::class)
 */
class Device
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Shop::class, inversedBy="devices")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $shop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $shop_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mac;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="devices")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function setMac(?string $mac): self
    {
        $this->mac = $mac;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

}

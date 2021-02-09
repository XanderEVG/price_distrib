<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 */
class City
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;


    /**
    * @OneToMany(targetEntity="Shop", mappedBy="city")
    */
    private ?Collection $shops;


    public function __construct() {
        $this->shops = new ArrayCollection();
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

    public function getShops(): ?array
    {
        return $this->shops->getValues();
    }

    public function addShop(Shop $shop): self
    {
        if (in_array($shop, $this->shops->getValues()) === false) {
            $this->shops[] = $shop;
        }
        return $this;
    }

    public function setShops(?array $shops): self
    {
        $this->shops = $shops;

        return $this;
    }
}

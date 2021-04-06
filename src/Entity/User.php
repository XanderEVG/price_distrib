<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`",
 *    uniqueConstraints={
 *        @UniqueConstraint(name="user_email_unique",
 *            columns={"email"})
 *    })
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private ?string $username;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private ?string $fio;

    /**
     * Роли пользователя
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private ?string $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $email;

     /**
     * @ManyToMany(targetEntity="City")
     * @JoinTable(name="users_cities",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@JoinColumn(name="city_id", referencedColumnName="id", onDelete="cascade")}
     *      )
     */

    private ?Collection $cities;

    /**
     * @ManyToMany(targetEntity="Shop")
     * @JoinTable(name="users_shops",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@JoinColumn(name="shop_id", referencedColumnName="id", onDelete="cascade")}
     *      )
     */
    private ?Collection $shops;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->shops = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFio(): ?string
    {
        return $this->fio;
    }

    public function setFio(?string $fio): self
    {
        $this->fio = $fio;

        return $this;
    }

    public static function availsbleRoles(): array
    {
        return [
            'ROLE_USER' => 'Пользователь',
            'ROLE_ADMIN' => 'Администратор',
        ];
    }
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCities(): ?array
    {
        return $this->cities->getValues();
    }

    public function addCity(City $city): self
    {
        if ($this->cities != null) {
            if (in_array($city, $this->cities->getValues()) === false) {
                $this->cities[] = $city;
            }
        } else {
            $this->cities[] = $city;
        }

        return $this;
    }

    public function setCities(?array $cities): self
    {
        if ($cities == null) {
            $this->cities = new ArrayCollection();
        } else {
            $this->cities = $cities;
        }

        return $this;
    }


    public function getShops(): ?array
    {
        return $this->shops->getValues();
    }

    public function addShop(Shop $shop): self
    {
        if ($this->shops != null) {
            if (in_array($shop, $this->shops->getValues()) === false) {
                $this->shops[] = $shop;
            }
        } else {
            $this->shops[] = $shop;
        }
        return $this;
    }

    public function setShops(?array $shops): self
    {
        if ($shops == null) {
            $this->shops = new ArrayCollection();
        } else {
            $this->shops = $shops;
        }

        return $this;
    }
}

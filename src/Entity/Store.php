<?php

namespace App\Entity;

use App\Repository\StoreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StoreRepository::class)]
class Store
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message:"Veuillez saisir le nom de votre boutique")]
    private ?string $name = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message:"Veuillez saisir une adresse email")]
    #[Assert\Length(max : 180)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 14)]
    #[Assert\NotBlank(message:"Veuillez saisir un numéro de Siret à 14 chiffres")]
    #[Assert\Length(
        min : 14,
        max : 14
    )]
    private ?string $siret_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"Veuillez saisir une description")]
    #[Assert\Length(
        min: 20,
        max: 2000,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères',
        maxMessage: 'La description ne doit pas excéder {{ limit }} caractères',
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $road_specificity = null;

    #[ORM\OneToMany(mappedBy: 'store', targetEntity: StoreHours::class, cascade: ["persist"])]
    private Collection $storeHours;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'stores', cascade: ["persist"])]
    private Collection $products;

    #[ORM\OneToMany(mappedBy: 'stores', targetEntity: Address::class, cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $addresses;

    #[ORM\ManyToOne(inversedBy: 'ownedStores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favourites')]
    private Collection $favouritesUsers;

    #[ORM\Column(length: 50)]
    private ?string $slug = null;


    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->storeHours = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->favouritesUsers = new ArrayCollection();
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


    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getSiretNumber(): ?string
    {
        return $this->siret_number;
    }

    public function setSiretNumber(string $siret_number): self
    {
        $this->siret_number = $siret_number;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

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

    public function getRoadSpecificity(): ?string
    {
        return $this->road_specificity;
    }

    public function setRoadSpecificity(?string $road_specificity): self
    {
        $this->road_specificity = $road_specificity;

        return $this;
    }

    /**
     * @return Collection<int, storeHours>
     */
    public function getStoreHours(): Collection
    {
        return $this->storeHours;
    }

    public function addStoreHour(storeHours $storeHour): self
    {
        if (!$this->storeHours->contains($storeHour)) {
            $this->storeHours->add($storeHour);
            $storeHour->setStore($this);
        }

        return $this;
    }

    public function removeStoreHour(storeHours $storeHour): self
    {
        if ($this->storeHours->removeElement($storeHour)) {
            // set the owning side to null (unless already changed)
            if ($storeHour->getStore() === $this) {
                $storeHour->setStore(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    function setAddresses($addresses): static
    {

        $this->addresses = $addresses;

        return $this;

    }

    public function addAddresses(address $addresses): self
    {
        if (!$this->addresses->contains($addresses)) {
            $this->$addresses->add($addresses);
            $addresses->setStores($this);
        }

        return $this;
    }

    public function removeAddresses(address $addresses): self
    {
        if ($this->addresses->removeElement($addresses)) {
            // set the owning side to null (unless already changed)
            if ($addresses->getStores() === $this) {
                $addresses->setStores(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavouritesUsers(): Collection
    {
        return $this->favouritesUsers;
    }

    public function addFavouritesUser(User $favouritesUser): self
    {
        if (!$this->favouritesUsers->contains($favouritesUser)) {
            $this->favouritesUsers->add($favouritesUser);
            $favouritesUser->addFavourite($this);
        }

        return $this;
    }

    public function removeFavouritesUser(User $favouritesUser): self
    {
        if ($this->favouritesUsers->removeElement($favouritesUser)) {
            $favouritesUser->removeFavourite($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

}

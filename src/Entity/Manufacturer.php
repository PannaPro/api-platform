<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/** Manufacturer */
#[ORM\Entity]
#[
    ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
    ]
    ),
    ApiResource(
        normalizationContext: ['groups' => ['manufacturer.read']],
        denormalizationContext: ['groups' => ['manufacturer.write']],
        paginationItemsPerPage: 10,
    ),
    ApiResource(
        uriTemplate: '/manufacturers/{id}/products',
        operations: [ new Get() ],
        uriVariables: [
            'id' => new Link(fromClass: Manufacturer::class),
        ]
    )
]
class Manufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the manufacturer */
    #[ORM\Column(nullable: true)]
    #[
        Assert\NotBlank,
        Groups('product.read'),
    ]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[
        Assert\NotBlank,
        Groups('product.read'),
    ]
    private ?string $description = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[
        Assert\NotBlank,
        Groups('product.read'),
    ]
    private ?string $countryCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $listedDate = null;

    /**
     * @var Collection<int, Product>|null
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'manufacturer')]
    private ?Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Manufacturer
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return \DateTime|null
     */
    public function getListedDate(): ?\DateTime
    {
        return $this->listedDate;
    }

    /**
     * @param \DateTime|null $listedDate
     * @return void
     */
    public function setListedDate(\DateTime|null $listedDate): void
    {
        $this->listedDate = $listedDate;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setManufacturer($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getManufacturer() === $this) {
                $product->setManufacturer(null);
            }
        }

        return $this;
    }
}
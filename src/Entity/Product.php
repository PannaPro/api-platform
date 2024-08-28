<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[
    ApiResource(
        operations: [
            new GetCollection(
                security: "is_granted('ROLE_ADMIN')"
            ),
            new Post(
                security: "is_granted('ROLE_ADMIN')"
            ),
            new Get(
                security: "is_granted('ROLE_USER')"
            ),
            new Put(
                security: "is_granted('ROLE_ADMIN')"
            ),
            new Patch(
                // user must be Admin AND owner of object
                security: "is_granted('ROLE_ADMIN') and object.getOwner() == user",
                securityMessage: 'This object can only be updated by owner'
            ),
        ],
        normalizationContext: ['groups' => ['product.read']],
        denormalizationContext: ['groups' => ['product.write']],
        paginationItemsPerPage: 10,
        security: "is_granted('ROLE_USER')",
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilter::STRATEGY_PARTIAL,
            'description' => SearchFilter::STRATEGY_PARTIAL,
            'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT,
            'manufacturer.id' => SearchFilter::STRATEGY_EXACT,
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'issueDate'
        ]
    ),
]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** The name of the product */
    #[ORM\Column(nullable: true)]
    #[
        Groups(['product.read', 'product.write', 'manufacturer.read']),
    ]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[
        Groups(['product.read', 'product.write']),
    ]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[
        Groups(['product.read', 'product.write']),
    ]
    private ?\DateTime $issueDate = null;

    #[
        Groups(['product.read', 'product.write']),
    ]
    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?User $owner = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     * @return \DateTime|null
     */
    public function getIssueDate(): ?\DateTime
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTime|null $issueDate
     */
    public function setIssueDate(?\DateTime $issueDate): void
    {
        $this->issueDate = $issueDate;
    }


    public function getManufacturer(): ?Manufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
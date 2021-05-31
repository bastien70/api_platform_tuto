<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\ApiPlatform\CheeseSearchFilter;
use App\Dto\CheeseListingInput;
use App\Dto\CheeseListingOutput;
use App\Repository\CheeseListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use App\Validator as App;

/**
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 * @ORM\EntityListeners({"App\Doctrine\CheeseListingSetOwnerListener"})
 * @App\ValidIsPublished()
 */
#[
    ApiResource(
        collectionOperations: [
            'get',
            'post' => [
                'security' => "is_granted('ROLE_USER')",
                'denormalization_context' => [
                    'groups' => ['cheese:write', 'cheese:collection:post']
                ]
            ],
        ],
        itemOperations: [
            'get' => [
                'normalization_context' => [
                    'groups' => ['cheese:read', 'cheese:item:get']
                ],
            ],
            'put' => [
                'security' => "is_granted('cheese_edit', object)",
                'security_message' => 'Only the creator can edit a cheese listing'
            ],
            'delete' => [
                'security' => "is_granted('ROLE_ADMIN')"
            ],
        ],
        shortName: 'cheese',
        denormalizationContext: ['groups' => ['cheese:write']],
        formats: [
            'jsonld', 'json', 'html', 'jsonhal',
            'csv' => ['text/csv']
        ],
        input: CheeseListingInput::class,
        normalizationContext: ['groups' => ['cheese:read']],
        output: CheeseListingOutput::class,
        paginationItemsPerPage: 10
    ),
    ApiFilter(BooleanFilter::class, properties: ['isPublished']),
    ApiFilter(SearchFilter::class, properties: [
        'title' => 'partial',
        'description' => 'partial',
        'owner' => 'exact',
        'owner.username' => 'partial'
    ]),
    ApiFilter(RangeFilter::class, properties: ['price']),
    ApiFilter(PropertyFilter::class),
    ApiFilter(
        CheeseSearchFilter::class,
        arguments: ['useLike' => true]
    )
]
class CheeseListing
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
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public function __construct(string $title = null)
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

//    public function setTitle(string $title): self
//    {
//        $this->title = $title;
//
//        return $this;
//    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

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
}

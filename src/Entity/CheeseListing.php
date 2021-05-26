<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\CheeseListingRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 */
#[
    ApiResource(
        collectionOperations: [
            'get',
            'post' => [
                'security' => "is_granted('ROLE_USER')"
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
        formats: [
            'jsonld', 'json', 'html', 'jsonhal',
            'csv' => ['text/csv']
        ],
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
    ApiFilter(PropertyFilter::class)
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
    #[
        Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write']),
        NotBlank(),
        Length(
            min: 2,
            max: 50,
            minMessage: 'Describe your cheese in 2 chars or more',
            maxMessage: 'Describe your cheese in 50 chars or less',
        )
    ]
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    #[
        Groups(['cheese:read']),
        NotBlank(),
    ]
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    #[
        Groups(['cheese:read', 'cheese:write', 'user:read', 'user:write']),
        NotBlank(),
    ]
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
    #[
        Groups(['cheese:read', 'cheese:write']),
        Valid()
    ]
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

    #[Groups(['cheese:read'])]
    public function getShortDescription(): ?string
    {
        if(strlen($this->description) > 40)
        {
            return $this->description;
        }

        return substr($this->description, 0, 40). '...';
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

    /**
     * The description of the cheese as raw text.
     * @param string $description
     * @return $this
     */
    #[
        Groups(['cheese:write', 'user:write']),
        SerializedName('description')
    ]
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

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

    /**
     * How long in text that this cheese listing wad added.
     * @return string
     */
    #[Groups(['cheese:read'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->createdAt)->diffForHumans();
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
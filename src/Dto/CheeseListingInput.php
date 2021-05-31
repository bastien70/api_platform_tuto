<?php


namespace App\Dto;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Validator as App;

class CheeseListingInput
{
    #[
        Groups(['cheese:write', 'user:write']),
        NotBlank(),
        Length(
            min: 2,
            max: 50,
            minMessage: 'Describe your cheese in 2 chars or more',
            maxMessage: 'Describe your cheese in 50 chars or less',
        )
    ]
    public ?string $title = null;

    #[
        Groups(['cheese:write', 'user:write']),
        NotBlank(),
    ]
    public ?int $price = null;

    /**
     * @App\IsValidOwner()
     */
    #[Groups(['cheese:collection:post'])]
    public ?User $owner = null;

    #[Groups(['cheese:write'])]
    public ?bool $isPublished = false;

    #[NotBlank()]
    public ?string $description = null;

    public static function createFromEntity(?CheeseListing $cheeseListing): self
    {
        $dto = new CheeseListingInput();
        // not an edit, so just return an empty DTO
        if (!$cheeseListing) {
            return $dto;
        }

        $dto->title = $cheeseListing->getTitle();
        $dto->price = $cheeseListing->getPrice();
        $dto->description = $cheeseListing->getDescription();
        $dto->owner = $cheeseListing->getOwner();
        $dto->isPublished = $cheeseListing->getIsPublished();

        return $dto;
    }

    public function createOrUpdateEntity(?CheeseListing $cheeseListing): CheeseListing
    {
        if(!$cheeseListing)
        {
            $cheeseListing = new CheeseListing($this->title);
        }

        $cheeseListing->setDescription($this->description)
            ->setPrice($this->price)
            ->setOwner($this->owner)
            ->setIsPublished($this->isPublished);

        return $cheeseListing;
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
}
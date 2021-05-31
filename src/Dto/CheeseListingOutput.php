<?php


namespace App\Dto;


use App\Entity\CheeseListing;
use App\Entity\User;
use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

class CheeseListingOutput
{
    /**
     * The title of this listing
     */
    #[Groups(['cheese:read', 'user:read'])]
    public ?string $title = null;

    /**
     * The description of this listing
     */
    #[Groups(['cheese:read'])]
    public ?string $description = null;

    /**
     * The price of this listing
     */
    #[Groups(['cheese:read', 'user:read'])]
    public ?int $price = null;

    public ?\DateTimeInterface $createdAt = null;

    /**
     * The owner of this listing
     */
    #[Groups(['cheese:read'])]
    public ?User $owner = null;

    public static function createFromEntity(CheeseListing $cheeseListing): self
    {
        $output = new CheeseListingOutput();

        $output->title = $cheeseListing->getTitle();
        $output->description = $cheeseListing->getDescription();
        $output->price = $cheeseListing->getPrice();
        $output->createdAt = $cheeseListing->getCreatedAt();
        $output->owner = $cheeseListing->getOwner();
        return $output;
    }

    #[Groups(['cheese:read'])]
    public function getShortDescription(): ?string
    {
        if(strlen($this->description) < 40)
        {
            return $this->description;
        }

        return substr($this->description, 0, 40). '...';
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
}
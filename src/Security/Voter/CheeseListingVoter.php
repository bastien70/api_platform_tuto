<?php

namespace App\Security\Voter;

use App\Entity\CheeseListing;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CheeseListingVoter extends Voter
{
    public const CHEESE_EDIT = 'cheese_edit';

    protected array $attributes = [
        self::CHEESE_EDIT
    ];

    public function __construct(private Security $security){}

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, $this->attributes, true)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if($token instanceof NullToken)
        {
            // ...
        }

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var CheeseListing $subject */

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::CHEESE_EDIT:
                return $subject->getOwner() === $user || $this->security->isGranted('ROLE_ADMIN');
        }

        throw new \Exception(sprintf('Unhandled attribute "%s"', $attribute));
    }
}

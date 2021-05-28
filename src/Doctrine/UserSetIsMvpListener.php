<?php


namespace App\Doctrine;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserSetIsMvpListener
{
    public function __construct(private Security $security){}

    public function postLoad(User $user)
    {
        $user->setIsMvp(str_contains($user->getUsername(), 'cheese'));
    }
}
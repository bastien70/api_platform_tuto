<?php


namespace App\DataProvider;


use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\DenormalizedIdentifiersAwareItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;

class UserDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface, DenormalizedIdentifiersAwareItemDataProviderInterface
{
    public function __construct(private CollectionDataProviderInterface $collectionDataProvider, private ItemDataProviderInterface $itemDataProvider, private Security $security){}

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        /** @var User[] $users */
        $users = $this->collectionDataProvider->getCollection($resourceClass, $operationName, $context);

        $currentUser = $this->security->getUser();

        foreach($users as $user)
        {
            // now handled in listener
//            $user->setIsMe($currentUser === $user);
        }

        return $users;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === User::class;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var User|null $item */
        $item = $this->itemDataProvider->getItem($resourceClass, $id, $operationName, $context);

        if(!$item)
        {
            return null;
        }

        return $item;
    }
}
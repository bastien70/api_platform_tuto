<?php


namespace App\ApiPlatform;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\CheeseListing;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CheeseListingIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private Security $security){}

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    /**
     * Query Extension - Lors d'une requête GET (item ou collection)
     * Si user non authentifié => add where published = true (seulement les cheese publiés)
     * Si user authentifié => add where published = true or cheese.owner = user (tous les cheese de l'user publiés et non publiés, et les cheeses publiés des autres)
     *
     * @param string $resourceClass
     * @param QueryBuilder $queryBuilder
     */
    private function addWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== CheeseListing::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if(!$this->security->getUser())
        {
            $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))
                ->setParameter('isPublished', true);
        } else {
            $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished OR %s.owner = :owner', $rootAlias, $rootAlias))
                ->setParameter('isPublished', true)
                ->setParameter('owner', $this->security->getUser());
        }

    }
}
<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\CheeseListing;
use App\Entity\CheeseNotification;
use Doctrine\ORM\EntityManagerInterface;

class CheeseListingDataPersister implements DataPersisterInterface
{
    public function __construct(private DataPersisterInterface $decoratedDataPersister, private EntityManagerInterface $manager){}

    public function supports($data): bool
    {
        return $data instanceof CheeseListing;
    }

    /**
     * @param CheeseListing $data
     */
    public function persist($data)
    {
        $originalData = $this->manager->getUnitOfWork()->getOriginalEntityData($data);
        $wasAlreadyPublished = $originalData['isPublished'] ?? false;

        if($data->getIsPublished() && !$wasAlreadyPublished)
        {
            $notification = new CheeseNotification($data, 'Cheese listing was published!');
            $this->manager->persist($notification);
            $this->manager->flush();
        }

        $this->decoratedDataPersister->persist($data);
    }

    public function remove($data)
    {
        $this->decoratedDataPersister->remove($data);
    }
}
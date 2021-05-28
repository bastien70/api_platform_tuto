<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\DailyStats;
use Psr\Log\LoggerInterface;

class DailyStatsDataPersister implements DataPersisterInterface
{
    public function __construct(private LoggerInterface $logger){}

    public function supports($data): bool
    {
        return $data instanceof DailyStats;
    }

    /**
     * @param DailyStats $data
     */
    public function persist($data)
    {
        $this->logger->info(sprintf('Update the visitors to "%d"', $data->totalVisitors));
    }

    public function remove($data)
    {
        throw new \Exception('not supported');
    }
}
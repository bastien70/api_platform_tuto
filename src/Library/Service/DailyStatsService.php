<?php


namespace App\Library\Service;


use App\Library\Util\RequestActions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DailyStatsService extends AbstractService implements RequestActions
{

    public const BASE_PATH = '/daily-stats';

    /**
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function all(array $params = []): array
    {
        return $this->request('GET', self::BASE_PATH, [
            'query' => $params
        ]);
    }

    /**
     * @param string $dateString
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function retrieve(string $dateString, array $params = []): array
    {
        return $this->request('GET', $this->buildPath($dateString), $params);
    }

    /**
     * @throws \Exception
     */
    public function create(array $params = []): array
    {
        throw new \Exception('Route not found');
    }

    /**
     * @param string $dateString
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function update(string $dateString, array $params = []): array
    {
        return $this->request('PUT', $this->buildPath($dateString), [
            'json' => $params
        ]);
    }

    /**
     * @throws \Exception
     */
    public function delete(string $id, array $params = []): array
    {
        throw new \Exception('Route not found');
    }

    private function buildPath(string $dateString): string
    {
        return sprintf('%s/%s', self::BASE_PATH, $dateString);
    }
}
<?php


namespace App\Library\Service;


use App\Library\Util\RequestActions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CheeseService extends AbstractService implements RequestActions
{
    public const BASE_PATH = '/cheeses';

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
     * @param string $id
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function retrieve(string $id, array $params = []): array
    {
        return $this->request('GET', $this->buildPath($id), $params);
    }

    /**
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function create(array $params = []): array
    {
        return $this->request('POST', self::BASE_PATH, [
            'json' => $params
        ]);
    }

    /**
     * @param string $id
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function update(string $id, array $params = []): array
    {
        return $this->request('PATCH', $this->buildPath($id), [
            'json' => $params,
        ]);
    }

    /**
     * @param string $id
     * @param array $params
     * @return array
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function delete(string $id, array $params = []): array
    {
        return $this->request('DELETE', $this->buildPath($id), $params);
    }

    private function buildPath(int $id): string
    {
        return sprintf('%s/%s', self::BASE_PATH, $id);
    }
}
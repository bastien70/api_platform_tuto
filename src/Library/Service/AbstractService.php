<?php


namespace App\Library\Service;


use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbstractService
{
    public const BASE_API = 'https://localhost:8000/api';

    public function __construct(private HttpClientInterface $client, private RequestStack $request){}

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function request(string $method, string $path, array $params = [])
    {
        $defaultOptions = [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
        ];

        $currentRequest = $this->request->getCurrentRequest();

        if($currentRequest && $currentRequest->cookies->has('BEARER'))
        {
            $defaultOptions['headers']['Authorization'] = sprintf('Bearer %s', $currentRequest->cookies->get('BEARER'));
        }

        $response = $this->client->request(
            $method,
            $this->buildPath($path),
            array_merge($defaultOptions, $params)
        );

        return $response->toArray();
    }

    private function buildPath(string $path): string
    {
        return self::BASE_API . $path;
    }
}
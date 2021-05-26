<?php


namespace App\Tests\Functional;


use App\Entity\User;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = self::createClient();
        $client->request('POST', '/api/users', [
            'json' => [
                'email' => 'cheeseplease@example.com',
                'username' => 'cheeseplease',
                'password' => 'brie'
            ]
        ]);

        self::assertResponseStatusCodeSame(201);

        $this->logIn($client, 'cheeseplease@example.com', 'brie');
    }

    public function testUpdateUser()
    {
        $client = self::createClient();
        $user = $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');

        $client->request('PUT', '/api/users/'.$user->getId(), [
            'json' => [
                'username' => 'newUsername',
                'roles' => ['ROLE_ADMIN'], //will be ignored
            ]
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'username' => 'newUsername'
        ]);

        $em = $this->getEntityManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($user->getId());
        self::assertEquals(['ROLE_USER'], $user->getRoles());
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetUser()
    {
        $client = self::createClient();
        $user = $this->createUserAndLogIn($client, 'cheeseplease@example.com', 'foo');
        $user->setPhoneNumber('555.123.4567');
        $em = $this->getEntityManager();
        $em->flush();

        $client->request('GET', '/api/users/'.$user->getId());

        self::assertJsonContains([
            'username' => 'cheeseplease'
        ]);

        $data = $client->getResponse()->toArray();
        self::assertArrayNotHasKey('phoneNumber', $data);

        // Refresh user and elevate
        $user = $em->getRepository(User::class)->find($user->getId());
        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();
        $this->logIn($client, 'cheeseplease@example.com', 'foo');

        $client->request('GET', '/api/users/'.$user->getId());

        self::assertJsonContains([
            'phoneNumber' => '555.123.4567'
        ]);
    }
}
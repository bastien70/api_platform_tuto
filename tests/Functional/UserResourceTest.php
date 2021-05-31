<?php


namespace App\Tests\Functional;


use App\Entity\User;
use App\Factory\UserFactory;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Uid\Uuid;

class UserResourceTest extends CustomApiTestCase
{
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

        $user = UserFactory::repository()->findOneBy(['email' => 'cheeseplease@example.com']);
        self::assertNotNull($user);
        self::assertJsonContains([
            '@id' => '/api/users/'.$user->getUuid()
        ]);

        $this->logIn($client, 'cheeseplease@example.com', 'brie');
    }

    public function testCreateUserWithUuid()
    {
        $client = self::createClient();

        $uuid = Uuid::v4();

        $client->request('POST', '/api/users', [
            'json' => [
                'id' => $uuid,
                'email' => 'cheeseplease@example.com',
                'username' => 'cheeseplease',
                'password' => 'brie'
            ]
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            '@id' => '/api/users/'.$uuid
        ]);
    }

    public function testUpdateUser()
    {
        $client = self::createClient();
        $user = UserFactory::new()->create();
        $this->logIn($client, $user);

        $client->request('PUT', '/api/users/'.$user->getUuid(), [
            'json' => [
                'username' => 'newUsername',
                'roles' => ['ROLE_ADMIN'], //will be ignored
            ]
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'username' => 'newUsername'
        ]);

        $user->refresh();

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
        $user = UserFactory::new()->create([
            'phoneNumber' => '555.123.4567',
            'username' => 'cheesehead'
        ]);
        $authenticatedUser = UserFactory::new()->create();
        $this->logIn($client, $authenticatedUser);

        $client->request('GET', '/api/users/'.$user->getUuid());
        self::assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'username' => $user->getUsername(),
            'isMvp' => true,
        ]);

        $data = $client->getResponse()->toArray();
        $this->assertArrayNotHasKey('phoneNumber', $data);
        $this->assertJsonContains([
            'isMe' => false,
        ]);

        // refresh the user & elevate
        $user->refresh();
        $user->setRoles(['ROLE_ADMIN']);
        $user->save();
        $this->logIn($client, $user);

        $client->request('GET', '/api/users/'.$user->getUuid());
        $this->assertJsonContains([
            'phoneNumber' => '555.123.4567',
            'isMe' => true,
        ]);
    }
}
<?php


namespace App\Tests\Functional;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\CheeseListing;
use App\Entity\User;
use App\Factory\CheeseListingFactory;
use App\Factory\CheeseNotificationFactory;
use App\Factory\UserFactory;
use App\Test\CustomApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class CheeseListingResourceTest extends CustomApiTestCase
{

    public function testCreateCheeseListing()
    {
        $client = self::createClient();

        $client->request('POST', '/api/cheeses', [
            'json' => []
        ]);

        self::assertResponseStatusCodeSame(401);

        $authenticatedUser = UserFactory::new()->create();
        $otherUser = UserFactory::new()->create();
        $this->logIn($client, $authenticatedUser);

        $cheesyData = [
            'title' => 'Mystery cheese ...',
            'description' => 'hellooooooooooo',
            'price' => 1000,
        ];

        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData
        ]);

        self::assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData + ['owner' => '/api/users/'.$otherUser->getId()]
        ]);

        // si ça marche pas c'est error 400
        self::assertResponseStatusCodeSame(422, 'not passing the correct owner');

        $client->request('POST', '/api/cheeses', [
            'json' => $cheesyData + ['owner' => '/api/users/'.$authenticatedUser->getId()]
        ]);

        self::assertResponseStatusCodeSame(201);

    }

    public function testUpdateCheeseListing()
    {
        $client = self::createClient();
        $user1 = UserFactory::new()->create();
        $user2 = UserFactory::new()->create();

        $cheeseListing = CheeseListingFactory::new()->published()->create([
            'owner' => $user1,
        ]);

        $this->logIn($client, $user2);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            // try to trick security by reassigning to this user
            'json' => [
                'title' => 'updated',
                'owner' => '/api/users/'.$user2->getId()
            ]
        ]);

        self::assertResponseStatusCodeSame(403, 'only author can updated');

        $this->logIn($client, $user1);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'title' => 'updated'
            ]
        ]);

        self::assertResponseStatusCodeSame(200);
    }

    public function testPublishCheeseListing()
    {
        $client = self::createClient();
        $user = UserFactory::new()->create();

        $cheeseListing = CheeseListingFactory::new()
            ->withLongDescription()
            ->create(['owner' => $user])
        ;

        $this->logIn($client, $user);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'isPublished' => true
            ]
        ]);

        self::assertResponseStatusCodeSame(200);
        $cheeseListing->refresh();
        self::assertTrue($cheeseListing->getIsPublished());
        CheeseNotificationFactory::repository()->assert()->count(1, 'There should be one notification about being published');

        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => [
                'isPublished' => true
            ]
        ]);

        CheeseNotificationFactory::repository()->assert()->count(1);
    }

    public function testPublishCheeseListingValidation()
    {
        $client = self::createClient();
        $user = UserFactory::new()->create();
        $adminUser = UserFactory::new()->create(['roles' => ['ROLE_ADMIN']]);
        $cheeseListing = CheeseListingFactory::new()
            ->create(['owner' => $user, 'description' => 'short']);

        // 1) the owner CANNOT publish with a short description
        $this->logIn($client, $user);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['isPublished' => true]
        ]);
        self::assertResponseStatusCodeSame(422, 'description is too short');

        // 2) an admin user CAN publish with a short description
        $this->logIn($client, $adminUser);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['isPublished' => true]
        ]);
        self::assertResponseStatusCodeSame(200, 'admin CAN publish a short description');
        $cheeseListing->refresh();
        self::assertTrue($cheeseListing->getIsPublished());

        // 3) a normal user CAN make other changes to their listing
        $this->logIn($client, $user);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['price' => 12345]
        ]);
        self::assertResponseStatusCodeSame(200, 'user can make other changes on short description');
        $cheeseListing->refresh();
        self::assertSame(12345, $cheeseListing->getPrice());

        // 4) a normal user CANNOT unpublish
        $this->logIn($client, $user);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['isPublished' => false]
        ]);
        self::assertResponseStatusCodeSame(422, 'normal user cannot unpublish');

        // 5) an admin user CAN unpublish
        $this->logIn($client, $adminUser);
        $client->request('PUT', '/api/cheeses/'.$cheeseListing->getId(), [
            'json' => ['isPublished' => false]
        ]);
        self::assertResponseStatusCodeSame(200, 'admin can unpublish');
        $cheeseListing->refresh();
        self::assertFalse($cheeseListing->getIsPublished());
    }

    public function testGetCheeseListingCollection()
    {
        $client = self::createClient();
        $user = UserFactory::new()->create();

        $factory = CheeseListingFactory::new(['owner' => $user]);
        // CL 1: unpublished
        $factory->create();

        // CL 2: published
        $cheeseListing2 = $factory->published()->create([
            'title' => 'cheese2',
            'description' => 'cheese',
            'price' => 1000,
        ]);

        // CL 3: published
        $factory->published()->create();

        $client->request('GET', '/api/cheeses');
        $this->assertJsonContains(['hydra:totalItems' => 2]);
        $this->assertJsonContains(['hydra:member' => [
            0 => [
                '@id' => '/api/cheeses/' . $cheeseListing2->getId(),
                '@type' => 'cheese',
                'title' => 'cheese2',
                'description' => 'cheese',
                'price' => 1000,
                'owner' => '/api/users/' . $user->getId(),
                'shortDescription' => 'cheese',
                'createdAtAgo' => '1 second ago',
            ]
        ]]);
    }

    public function testGetCheeseListingItem()
    {
        $client = static::createClient();
        $user = UserFactory::new()->create();
        $this->logIn($client, $user);
        $otherUser = UserFactory::new()->create();

        $cheeseListing1 = CheeseListingFactory::new()->create(['owner' => $otherUser]);

        $client->request('GET', '/api/cheeses/'.$cheeseListing1->getId());

        self::assertResponseStatusCodeSame(404);

        $response = $client->request('GET', '/api/users/'.$otherUser->getId());
        $data = $response->toArray();
        $this->assertEmpty($data['cheeseListings']);
    }
}
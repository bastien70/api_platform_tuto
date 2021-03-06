<?php


namespace App\Test;


use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CustomApiTestCase extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strpos($email, '@')));

        /** @var UserPasswordEncoderInterface $encoder */
        $encoder = self::$container->get(UserPasswordEncoderInterface::class);

        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);

        /** @var EntityManagerInterface $em */
        $em = self::$container->get(EntityManagerInterface::class);
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function logIn(Client $client, $userOrEmail, string $password = UserFactory::DEFAULT_PASSWORD)
    {
        if ($userOrEmail instanceof User || $userOrEmail instanceof Proxy) {
            $email = $userOrEmail->getEmail();
        } elseif (is_string($userOrEmail)) {
            $email = $userOrEmail;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 2 to "%s" should be a User, Foundry Proxy or string email, "%s" given',
                __METHOD__,
                is_object($userOrEmail) ? get_class($userOrEmail) : gettype($userOrEmail)
            ));
        }

        $client->request('POST', '/login', [
            'json' => [
                'email' => $email,
                'password' => $password
            ],
        ]);
        self::assertResponseStatusCodeSame(204);
    }

    protected function createUserAndLogIn(Client $client, string $email, string $password): User
    {
        $user = $this->createUser($email, $password);
        $this->logIn($client, $email, $password);

        return $user;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return self::$container->get('doctrine')->getManager();
    }
}
<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements DataPersisterInterface
{
    public function __construct(private EntityManagerInterface $manager, private UserPasswordEncoderInterface $encoder){}

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     */
    public function persist($data)
    {
        if($data->getPlainPassword()) {
            $data->setPassword($this->encoder->encodePassword($data, $data->getPlainPassword()));
            $data->eraseCredentials();
        }
        $this->manager->persist($data);
        $this->manager->flush();
    }

    public function remove($data)
    {
        $this->manager->remove($data);
        $this->manager->flush();
    }
}
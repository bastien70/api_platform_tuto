<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class UserDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private DataPersisterInterface $decoratedDataPersister,
        private UserPasswordEncoderInterface $encoder,
        private LoggerInterface $logger,
        private Security $security
    ){}

    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
     */
    public function persist($data, array $context = [])
    {
        if(($context['item_operation_name'] ?? null) === 'put')
        {
            $this->logger->info(sprintf('User %s is being updated', $data->getId()));
        }

        if(!$data->getId())
        {
            // take any actions needed for a new user
            // send registration email
            // integrate into some CRM or payment system
            $this->logger->info(sprintf('User %s just registred! Eureka!', $data->getEmail()));
        }

        if($data->getPlainPassword()) {
            $data->setPassword($this->encoder->encodePassword($data, $data->getPlainPassword()));
            $data->eraseCredentials();
        }

        // now handled in listener
//        $data->setIsMe($this->security->getUser() === $data);

        $this->decoratedDataPersister->persist($data);
    }

    public function remove($data, array $context = [])
    {
        $this->decoratedDataPersister->remove($data);
    }
}
<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class SetIsMeOnCurrentSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security){}

    public function onRequestEvent(RequestEvent $event)
    {
        if(!$event->isMasterRequest()) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if(!$user) {
            return;
        }

        $user->setIsMe(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}

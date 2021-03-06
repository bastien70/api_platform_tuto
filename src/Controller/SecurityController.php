<?php


namespace App\Controller;


use ApiPlatform\Core\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login', methods: ['POST'])]
    public function login(IriConverterInterface $iriConverter)
    {
        if(!$this->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->json([
                'error' => 'Invalid login request : check that the Content-Type header is application/json'
            ], 400);
        }

        return new Response(null, 204, [
            'Location' => $iriConverter->getIriFromItem($this->getUser())
        ]);
    }

    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin()
    {
//        $user = $this->getUser();
//
//        return $this->json([
//            'username' => $user->getUsername(),
//            'roles' => $user->getRoles()
//        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception('should not be reached');
    }
}
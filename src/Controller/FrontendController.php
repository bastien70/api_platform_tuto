<?php

namespace App\Controller;

use App\Library\ApiClient;
use App\Service\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Controller to render a basic "homepage".
 */
class FrontendController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepage(SerializerInterface $serializer)
    {
        return $this->render('frontend/homepage.html.twig', [
            'user' => $serializer->serialize($this->getUser(), 'jsonld')
        ]);
    }

    /**
     * @Route("/test", name="test_page")
     */
    public function test(ApiClient $client)
    {
        $response = $client->cheeses->create([
            'title' => 'tisdcsfdvtisdtbcvikf',
            'price' => 50000,
            'description' => 'ahadffvscbdvdhahah'
        ]);

        dd($response);
        return $this->redirectToRoute('homepage');
    }
}

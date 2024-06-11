<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExternalApiController extends AbstractController
{
    /**
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     */
    #[Route('api/external/getApi', name: 'external_api')]
    public function getExternalApi(HttpClientInterface $httpClient): JsonResponse
    {
        $response = $httpClient->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );
        return new JsonResponse($response->getContent(), $response->getStatusCode(), [], true); //affiche les données Json d'une autre API mais le ne les stocke pas en BDD
    }
}

    

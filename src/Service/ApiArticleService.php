<?php

namespace App\Service;

use Exception;
use DateTimeImmutable;
use App\Entity\Articles;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiArticleService
{
    private $httpClient;
    private $entityManager;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function fetchAndSaveArticlesFromApi(string $apiUrl, array $mapping)
    {
        try {
            $response = $this->httpClient->request('GET', $apiUrl);

            // Vérification des url sinon erreur
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new Exception("HTTP/$statusCode returned for \"$apiUrl\"");
            }

            $articlesData = $response->toArray();

            // Debug: Print the structure of the response data
            $this->logger->info('API Response', ['data' => $articlesData]);
            if (!is_array($articlesData)) {
                throw new \Exception("Invalid API response format for \"$apiUrl\"");
            }

            foreach ($articlesData as $item) {

                // Debug: Print the structure of each item
                $this->logger->info('API Item', ['item' => $item]);
                // Ensure item is an array
                if (!is_array($item)) {
                    throw new \Exception("Invalid item format in API response for \"$apiUrl\"");
                }

                $article = new Articles();
                //Je fais un mapping api / articles que je vais enregistrer pour chaque API dans config/services.yaml
                $article->setTitle($item[$mapping['title']]);
                $article->setContent($item[$mapping['content']]);
                $article->setUrl($item[$mapping['url']]);
                $article->setImageURL($item[$mapping['imageURL']]);
                $article->setSource($item[$mapping['source']]);
                if (isset($item[$mapping['author']])) {
                    $article->setAuthor($item[$mapping['author']] ?? null);
                }
                $article->setPublishedAt(new DateTimeImmutable($item[$mapping['publishedAt']]));
                // Vérifier les articles déjà enregistrés
                $existingArticle = $this->entityManager->getRepository(Articles::class)->findOneBy(['title' => $item[$mapping['title']]]);
                if (!$existingArticle) {
                    $this->entityManager->persist($article);
                }
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            $this->logger->error('Error fetching articles: ' . $apiUrl, ['exception' => $e]);
            throw $e;
        }
    }
}

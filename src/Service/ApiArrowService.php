<?php

namespace App\Service;

use Exception;
use DateTimeImmutable;
use App\Entity\Articles;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiArrowService
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

    public function fetchAndSaveArticlesFromArrow(string $apiUrl)
    {
        try {
            $response = $this->httpClient->request('GET', $apiUrl);

            // Vérification des url sinon erreur
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new Exception("HTTP/$statusCode returned for \"$apiUrl\"");
            }

            $data = $response->toArray();

            // Debug: Print the structure of the response data
            $this->logger->info('API Response', ['data' => $data]);
            if (!is_array($data)) {
                throw new \Exception("Invalid API response format for \"$apiUrl\"");
            }

            if (isset($data['articles']) && is_array($data['articles'])) {
                foreach ($data['articles'] as $articleData) {
                    // Access article data here and process accordingly
                    $title = $articleData['title'] ?? null;
                    $content = $articleData['description'] ?? null;
                    $url = $articleData['url'] ?? null;
                    $imageUrl = $articleData['urlToImage'] ?? null;
                    $source = $articleData['source']['name'] ?? null;
                    $author = $articleData['author'] ?? null;
                    $publishedAt = $articleData['publishedAt'] ?? null;

            
                    // Mapping and saving logic
                    // Example:
                    $article = new Articles();
                    $article->setTitle($title);
                    $article->setContent($content);
                    $article->setUrl($url);
                    $article->setImageURL($imageUrl);
                    $article->setSource($source);
                    if (isset($author)) {
                        $article->setAuthor($author);
                    }
                    $article->setPublishedAt(new DateTimeImmutable($publishedAt));
                
                    // Vérifier les articles déjà enregistrés
                    $existingArticle = $this->entityManager->getRepository(Articles::class)->findOneBy(['title' => $title]);
                    if (!$existingArticle) {
                        $this->entityManager->persist($article);
                    }
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

<?php

namespace App\Service;

use Exception;
use DateTimeImmutable;
use App\Entity\Articles;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiRssService
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

    public function fetchAndSaveArticlesFromRSS(string $rssUrl)
    {
        try {
            $response = $this->httpClient->request('GET', $rssUrl);

            $xml = simplexml_load_string($response->getContent());
            if ($xml === false) {
                throw new Exception("Failed to parse RSS feed from \"$rssUrl\"");
            }

            foreach ($xml->channel->item as $item) {
                $title = (string) $item->title;
                $content = (string) $item->description;
                $url = (string) $item->link;
                $publishedAt = new DateTimeImmutable((string) $item->pubDate);
                $imageUrl = null;
                // Vérifiez si media:content existe avant de tenter de l'extraire
                if (isset($item->children('media', true)->content)) {
                    $mediaContent = $item->children('media', true)->content;
                    $imageUrl = (string) $mediaContent->attributes()['url'];
                }
                $source = (string) $xml->channel->title; // Utiliser le titre du channel comme source
                $author = null; // Laisser l'auteur vide

                $article = new Articles();
                $article->setTitle($title);
                $article->setContent($content);
                $article->setUrl($url);
                $article->setPublishedAt($publishedAt);
                $article->setSource($source); 
                $article->setImageURL($imageUrl);
                $article->setAuthor($author);

                $existingArticle = $this->entityManager->getRepository(Articles::class)->findOneBy(['title' => $title]);
                if (!$existingArticle) {
                    $this->entityManager->persist($article);
                }
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Error fetching and saving articles from RSS feed: ' . $rssUrl, ['exception' => $e]);
            throw $e;
        }
    }
}
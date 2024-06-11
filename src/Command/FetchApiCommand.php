<?php

namespace App\Command;

use App\Service\ApiArticleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FetchApiCommand extends Command
{
    protected static $defaultName = 'app:fetch-articles-api';
    // command = php bin/console app:fetch-articles-api
    private $articleFetcher;
    private $params;

    public function __construct(ApiArticleService $apiArticleService, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->articleFetcher = $apiArticleService;
        $this->params = $params;
    }

    protected function configure()
    {
        $this->setDescription('Fetch articles from various APIs and save them to the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Si on fait source par source:
        // $apiUrl = 'https://example.com/api/articles'; // Remplacez par votre URL d'API
        // Si on préfère faire plusieurs sources d'un coup:
        $articleSources = $this->params->get('article_sources');

        try {
            foreach ($articleSources as $source) {
                $io->section('Fetching from: ' . $source['apiUrl']);
                $this->articleFetcher->fetchAndSaveArticlesFromApi($source['apiUrl'], $source['mapping']);
            }
            $io->success('Articles fetched and saved successfully.');    
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error fetching articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

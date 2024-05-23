<?php

namespace App\Command;

use App\Service\ApiArrowService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FetchArrowCommand extends Command
{
    protected static $defaultName = 'app:fetch-articles-arrow';

    private $articleFetcher;
    private $params;

    public function __construct(ApiArrowService $apiArrowService, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->articleFetcher = $apiArrowService;
        $this->params = $params;
    }

    protected function configure()
    {
        $this->setDescription('Fetch articles from various APIs with Arrow and save them to the database');
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
                $this->articleFetcher->fetchAndSaveArticlesFromArrow($source['apiUrl']);
            }
            $io->success('Articles fetched and saved successfully.');    
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error fetching articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}


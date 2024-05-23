<?php

namespace App\Command;

use App\Service\ApiRssService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FetchRssCommand extends Command
{
    protected static $defaultName = 'app:fetch-articles-rss';

    private $articleFetcher;
    private $params;

    public function __construct(ApiRssService $apiRssService, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->articleFetcher = $apiRssService;
        $this->params = $params;
    }

    protected function configure()
    {
        $this->setDescription('Fetch articles from Rss Xml and save them to the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Si on fait source par source:
        // $rssUrl = 'https://www.lemonde.fr/rss/une.xml';
        // Si on préfère faire plusieurs sources d'un coup:
        $articleSources = $this->params->get('article_sources');

        try {
            foreach ($articleSources as $source) {
                $io->section('Fetching from: ' . $source['rssUrl']);
                $this->articleFetcher->fetchAndSaveArticlesFromRss($source['rssUrl']);
            }
            $io->success('Articles fetched and saved successfully.');    
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error fetching articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}


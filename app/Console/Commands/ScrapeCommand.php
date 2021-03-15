<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\HttpClient;

class ScrapeCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $client = new Client(HttpClient::create(['timeout' => 60]));
        // Go to the symfony.com website
        $crawler = $client->request('GET', 'https://www.bbc.co.uk/news');



        $crawler->filter('.gs-c-promo-heading')->each(function ($node) {
            echo json_encode($node);
//            print $node->text()."\n";
        });
        return 0;
    }
}

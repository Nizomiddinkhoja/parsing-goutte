<?php

namespace App\Http\Controllers;

use Goutte\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class ScrapingController extends Controller
{
//    private $sort = [
//        'views',
//        'week',
//        'month'
//    ];

    private $data = [];
    private $queryParams = '';
    private $counter = 0;

    public function index(Client $client, Request $request)
    {

        $this->getQueryParams($request);
        ($request->query('link')) ? $link = ($request->query('link')) : $link = 'sort';

        switch ($link) {
            case 'sort':
                $pageUrl = 'https://z1.fm/' . $this->queryParams;
                break;
            case 'artist':
                $pageUrl = 'https://z1.fm/artist/' . $this->queryParams;
                break;
            case 'song':
                $pageUrl = 'https://z1.fm/song/' . $this->queryParams;
                break;
            case 'search':
                $pageUrl = 'https://z1.fm/search' . $this->queryParams;
                break;
            default:
                $pageUrl = 'https://z1.fm/' . $this->queryParams;
        }

        $crawler = $client->request('GET', $pageUrl);

        $result = $this->extractSongsFrom($crawler);
        return response($result, 200);
    }


    public function extractSongsFrom(Crawler $crawler)
    {
        $selector = '.song-wrap-xl';

        $crawler->filter($selector)->each(function (Crawler $node) {

            $dataPrev = (int)$node->filter('.song.song-xl')->attr('data-prev');
            $dataNext = (int)$node->filter('.song.song-xl')->attr('data-next');
            $dataPlay = (int)$node->filter('.song.song-xl')->attr('data-play');

            ($node->filter('.song-img img.lazy')->count() > 0) ?
                $songImg = $node->filter('.song-img img.lazy')->attr('data-original') :
                $songImg = '';


            ($node->filter('.song-info .song-time')) ?
                $songTime = $node->filter('.song-info .song-time')->text() :
                $songTime = '';

            $dataSongContent = $node->filter('.song-content');

            $artistLink = $dataSongContent->filter('.song-artist a')->attr('href');
            $artistName = $dataSongContent->filter(".song-artist a span")->text();

            $songLink = $dataSongContent->filter('.song-name a')->attr('href');
            $songName = $dataSongContent->filter(".song-name a span")->text();


            $songDownloadUrl = $node->filter('[data-sid="' . $dataPlay . '"]')->attr('data-url');

            $data['dataPlay'] = $dataPlay;
            $data['dataPrev'] = $dataPrev;
            $data['dataNext'] = $dataNext;
            $data['songImg'] = $songImg;
            $data['songTime'] = $songTime;

            $items['artistLink'] = $artistLink;
            $items['artistName'] = $artistName;
            $items['songLink'] = $songLink;
            $items['songName'] = $songName;

            $data['songContent'] = $items;
            $data['songDownloadUrl'] = $songDownloadUrl;

            $this->data[] = $data;
        });
        return ($this->data);
    }

    public function getQueryParams($request)
    {
        foreach ($request->all() as $key => $item) {
            if ($key === 'link') {
                continue;
            }
            ($this->counter === 0) ? $concat = '?' : $concat = '&';
            $this->queryParams .= "$concat" . "$key=$item";
            $this->counter++;
        }
        $this->counter = 0;
    }

}

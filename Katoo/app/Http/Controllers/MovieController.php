<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tmdb\Laravel\TmdbServiceProvider;
use Tmdb\Laravel\Facades\Tmdb;

class MovieController extends Controller
{
    private $client;

    public function __construct() {
        $token = new \Tmdb\ApiToken(env('TMDB_API_KEY', 'localhost'));
        $this->client = new \Tmdb\Client($token);
    }

    public function getUpcoming($pageNum) {
        $repository = new \Tmdb\Repository\MovieRepository($this->client);
        $popularResponse = $repository->getPopular(array('page' => $pageNum));
        foreach($popularResponse->toArray() as $movie) {
            print_r($movie);
        }
    }
}

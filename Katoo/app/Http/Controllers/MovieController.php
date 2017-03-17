<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tmdb\Laravel\TmdbServiceProvider;
use Tmdb\Laravel\Facades\Tmdb;

class MovieController extends Controller
{
    private $client;
    private $repository;

    public function __construct() {
        $token = new \Tmdb\ApiToken(env('TMDB_API_KEY', 'localhost'));
        $this->client = new \Tmdb\Client($token);
        $this->repository = new \Tmdb\Repository\MovieRepository($this->client);
    }

    public function getPopular($pageNum) {
        $response = $this->repository->getPopular(array('page' => $pageNum));
        foreach($response->toArray() as $movie) {
            print_r($movie);
        }
    }

    public function getUpcoming($pageNum) {
        $response = $this->repository->getUpcoming(array('page' => $pageNum));
        foreach($response->toArray() as $movie) {
            print_r($movie);
        }
    }

    /* $movieId: imdb id. Return name, type, duration, synopsis, imdb rating, rotten tomatoes rating, and movie url */
    public function getDetails($movieId) {
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $response = $client->get('', [
                'query' => ['i' => $movieId,
                            'tomatoes' => 'true'
                            ]
            ])->getBody();
        $details = json_decode($response);
        print_r($details); // How to access: $details->Title
    }

    public function getReviews($movieId) {
        $response = $this->repository->getReviews($movieId);
        foreach($response->toArray() as $review) {
            print_r($review);
        }
    }
}

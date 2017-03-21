<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tmdb\Laravel\TmdbServiceProvider;
use Tmdb\Laravel\Facades\Tmdb;
use Illuminate\Support\Facades\DB;

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
        $response = $this->repository->getPopular(['page' => $pageNum]);
        $movies = [];
        foreach($response->toArray() as $movieResponse) {
            $genres = [];
            foreach($movieResponse->getGenres() as $genreResponse) {
                array_push($genres, $this->getGenreName($genreResponse->getId()));
            }
            $movie = [
                'id' => $movieResponse->getId(),
                'title' => $movieResponse->getTitle(),
                'poster_path' => env('TMDB_IMG_BASE_URL', 'localhost') . $movieResponse->getPosterPath(),
                'genre' => $genres,
                'tmdb_vote' => $movieResponse->getVoteAverage()
            ];
            array_push($movies, $movie);
        }
        return response()->json($movies);
    }

    public function getUpcoming($pageNum) {
        $response = $this->repository->getUpcoming(['page' => $pageNum]);
        $movies = [];
        foreach($response->toArray() as $movieResponse) {
            $genres = [];
            foreach($movieResponse->getGenres() as $genreResponse) {
                array_push($genres, $this->getGenreName($genreResponse->getId()));
            }
            $movie = [
                'id' => $movieResponse->getId(),
                'title' => $movieResponse->getTitle(),
                'poster_path' => env('TMDB_IMG_BASE_URL', 'localhost') . $movieResponse->getPosterPath(),
                'genre' => $genres,
                'tmdb_vote' => $movieResponse->getVoteAverage()
            ];
            array_push($movies, $movie);
        }
        return response()->json($movies);
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

    // Helper
    public function getGenreList() {
        $response = $this->client->getGenresApi()->getMovieGenres();
        DB::table('movie_genre')->insert($response['genres']);
        return $response['genres'];
    }

    public function getGenreName($id) {
        $genre = DB::table('movie_genre')->find($id);
        return $genre->name;
    }
}

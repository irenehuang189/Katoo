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
                'tmdb_id'       => $movieResponse->getId(),
                'title'         => $movieResponse->getTitle(),
                'poster_path'   => env('TMDB_IMG_BASE_URL', 'localhost') . $movieResponse->getPosterPath(),
                'genre'         => $genres,
                'tmdb_rating'     => $movieResponse->getVoteAverage()
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
                'tmdb_id'       => $movieResponse->getId(),
                'title'         => $movieResponse->getTitle(),
                'poster_path'   => env('TMDB_IMG_BASE_URL', 'localhost') . $movieResponse->getPosterPath(),
                'genre'         => $genres,
                'tmdb_rating'     => $movieResponse->getVoteAverage()
            ];
            array_push($movies, $movie);
        }
        return response()->json($movies);
    }

    public function getDetails($movieId) {
        $tmdbResponse = $this->client->getMoviesApi()->getMovie($movieId);
        // print_r($tmdbResponse);
        // die;

        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $imdbResponse = $client->request('GET', '', [
                'query' => ['i' => $tmdbResponse['imdb_id'],
                            'tomatoes' => 'true'
                            ],
                'verify' => false
            ])->getBody();
        // $imdbResponse = $client->get('', [
        //         'query' => ['i' => $tmdbResponse['imdb_id'],
        //                     'tomatoes' => 'true'
        //                     ],
        //         'verify' => false
        //     ])->getBody();
        $detailsResponse = json_decode($imdbResponse);
        $details = [
            'tmdb_id'       => $tmdbResponse['id'],
            'imdb_id'       => $tmdbResponse['imdb_id'],
            'title'         => $detailsResponse->Title,
            'genre'         => $detailsResponse->Genre,
            'duration'      => $detailsResponse->Runtime,
            'poster_path'   => $detailsResponse->Poster,
            'plot'          => $detailsResponse->Plot,
            'imdb_rating'   => $detailsResponse->imdbVotes,
            'url'           => env('IMDB_BASE_URL', 'localhost') . $tmdbResponse['imdb_id'],
            // 'tmdb_url'     => $tmdbResponse['tmdb_vote']
        ];
        return response()->json($details);
    }

    public function getReviews($movieId) {
        $response = $this->repository->getReviews($movieId);
        $reviews = [];
        foreach($response->toArray() as $reviewResponse) {
            $review = [
                'id'        => $reviewResponse->getId(),
                'content'   => $reviewResponse->getContent(),
                'url'       => $reviewResponse->getUrl()
            ];
            array_push($reviews, $review);
        }
        return response()->json($reviews);
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

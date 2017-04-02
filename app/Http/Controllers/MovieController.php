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

    public function getUpcoming($pageNum) {
        $dbController = new DatabaseController;
        $cinemaUpcoming = $dbController->getTitleOfUpcomingMovies(5*($pageNum-1), 5*$pageNum);
        // var_dump($cinemaUpcoming);
        // die;

        $movies = [];
        foreach($cinemaUpcoming as $movie) {
            // Get title, genre, poster path, imdb id from omdb api
            $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
            $query = http_build_query([
                    't'     => $movie->name,
                    'y'     => 2017
                ]);
            $omdbResponse = $client->request('GET', '?' . $query)->getBody();
            $movieResponse = json_decode($omdbResponse);

            if($movieResponse->Response == 'False') {
                $movie = [
                    'db_id'     => $movie->id,
                    'imdb_id'   => null,
                    'title'     => ucwords(strtolower($movie->name)),
                    'poster'    => $movie->poster != NULL ? $movie->poster : 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg',
                    'genre'     => $movie->genre != '' ? ucwords($movie->genre) : '-'
                ];
            } else {
                $movie = [
                    'db_id'     => $movie->id,
                    'imdb_id'   => $movieResponse->imdbID,
                    'title'     => $movieResponse->Title,
                    'poster'    => $movieResponse->Poster,
                    'genre'     => $movieResponse->Genre
                ];
            }
            array_push($movies, $movie);
        }
        return response()->json($movies);
    }

    public function getDetails($movieId) {
        $tmdbResponse = $this->client->getMoviesApi()->getMovie($movieId);

        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $imdbResponse = $client->request('GET', '', [
                'query' => ['i' => $tmdbResponse['imdb_id'],
                            'tomatoes' => 'true'
                            ],
                'verify' => false
            ])->getBody();

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

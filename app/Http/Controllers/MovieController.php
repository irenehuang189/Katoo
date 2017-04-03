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

    public function getNowPlaying($pageNum) {
        $dbController = new DatabaseController;
        $cinemaNowPlaying = $dbController->getTitleOfNowPlayingMovies(5*($pageNum-1), 5*$pageNum);

        $movies = [];
        foreach($cinemaNowPlaying as $movie) {
            // Get title, genre, poster path, imdb id from omdb api
            $movie = $this->getDetailsByName($movie->name);
            array_push($movies, $movie);
        }
        var_dump($movies);
        return response()->json($movies);
    }

    public function getUpcoming($pageNum) {
        $dbController = new DatabaseController;
        $cinemaUpcoming = $dbController->getTitleOfUpcomingMovies(5*($pageNum-1), 5*$pageNum);

        $movies = [];
        foreach($cinemaUpcoming as $movie) {
            // Get title, genre, poster path, imdb id from omdb api
            $movie = $this->getDetailsByName($movie->name);
            array_push($movies, $movie);
        }
        var_dump($movies);
        return response()->json($movies);
    }

    public function getDetailsById($imdbId, $dbId, $state) {
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $omdbResponse = $client->request('GET', '', [
                'query' => ['i'         => $imdbId,
                            'tomatoes'  => 'true',
                            'plot'      => 'full'
                            ]
            ])->getBody();
        $detailsResponse = json_decode($omdbResponse);

        if($detailsResponse->Response == 'False') {
            // Get details in database
            $dbController = new DatabaseController;
            $detailsResponse = $dbController->getDetailsById($dbId, $state);
            
            $details = [
                'imdb_id'       => $imdbId,
                'db_id'         => $dbId,
                'state'         => $state,
                'title'         => $detailsResponse->name,
                'genre'         => $detailsResponse->genre,
                'duration'      => $detailsResponse->duration,
                'imdb_rating'   => $detailsResponse->imdbRating,
                'ratings'       => null
            ];
        } else {
            $details = [
                'imdb_id'       => $imdbId,
                'db_id'         => $dbId,
                'state'         => $state,
                'title'         => $detailsResponse->Title,
                'genre'         => $detailsResponse->Genre,
                'duration'      => $detailsResponse->Runtime,
                'imdb_rating'   => $detailsResponse->imdbRating,
                'ratings'       => $detailsResponse->Ratings
            ];
        }

        return response()->json($details);
    }

    public function getDetailsByName($name) {
        // Get movie state from database
        $dbController = new DatabaseController;
        $detailsDbResponse = $dbController->getDetailsByName($name);
        $state = null;
        if($detailsDbResponse) {
            $state = $detailsDbResponse->state;
        }

        // Search movie to omdb
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $query = http_build_query([
            't'     => $name
        ]);
        $omdbResponse = $client->request('GET', '?' . $query)->getBody();
        $detailsResponse = json_decode($omdbResponse);

        if(!$detailsDbResponse && !$detailsResponse) {
            return response()->json([
                'error' => 'Nama film tidak ditemukan. Apakah kamu yakin itu judul film yang benar?'
            ]);
        }

        if($detailsResponse->Response == 'False') {
            return response()->json([
                'db_id'     => $detailsDbResponse->id,
                'imdb_id'   => null,
                'state'     => $state,
                'title'     => ucwords(strtolower($detailsDbResponse->name)),
                'poster'    => $detailsDbResponse->poster != NULL ? $detailsDbResponse->poster : 'http://ia.media-imdb.com/images/G/01/imdb/images/nopicture/large/film-184890147._CB522736516_.png',
                'genre'     => $detailsDbResponse->genre != '' ? ucwords(strtolower($detailsDbResponse->genre)) : '-'
            ]);
        }

        return response()->json([
            'db_id'     => $detailsDbResponse->id,
            'imdb_id'   => $detailsResponse->imdbID,
            'state'     => $state,
            'title'     => $detailsResponse->Title,
            'poster'    => $detailsResponse->Poster,
            'genre'     => $detailsResponse->Genre
        ]);
    }

    public function getReviews($movieId) {
        $find = $this->client->getFindApi()->findBy($movieId, [
            'external_source' => 'imdb_id'
        ]);
        if(empty((array)$find['movie_results'])) {
            return response()->json([
                'error' => 'Maaf tidak ada review untuk film ini :('
            ]);
        }

        $tmdbId = $find['movie_results']['0']['id'];
        $response = $this->repository->getReviews($tmdbId);
        if(!$response->getTotalPages()) {
            return response()->json([
                'error' => 'Maaf tidak ada review untuk film ini :('
            ]);
        }

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

    public function getCinema($dbId) {
        $dbController = new DatabaseController;
        $cinema = $dbController->getCinema($dbId);
        if(!$cinema) {
            return response()->json([
                'error' => 'Maaf, belum ada penayangan film ini di hari ini :('
            ]);
        }

        return response()->json($cinema);
    }

    public function getSchedule($dbId, $city) {
        $dbController = new DatabaseController;
        $schedule = $dbController->getSchedule($dbId, $city);
        if(!$schedule) {
            return response()->json([
                'error' => 'Maaf, belum ada penayangan film ini di hari ini :('
            ]);
        }

        return response()->json($schedule);
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

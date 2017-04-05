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
    private $noImageUrl = 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg';

    public function __construct() {
        $token = new \Tmdb\ApiToken(env('TMDB_API_KEY', 'localhost'));
        $this->client = new \Tmdb\Client($token);
        $this->repository = new \Tmdb\Repository\MovieRepository($this->client);
    }

    public function getNowPlaying($pageNum) {
        $dbController = new DatabaseController;
        $cinemaNowPlaying = $dbController->getTitleOfNowPlayingMovies(5*($pageNum-1), 5*$pageNum);
        if(!count($cinemaNowPlaying)) {
            return response()->json([
                'error' => 'Semua film yang sedang tayang sudah ditampilkan. Kamu memang cari film apa?'
            ]);
        }

        $movies = [];
        foreach($cinemaNowPlaying as $movie) {
            // Get title, genre, poster path, imdb id from omdb api
            $detailsResponse = $this->getUpcomingNowPlayingDetails($movie->name);
            $movie = json_decode($detailsResponse->getContent());
            array_push($movies, $movie);
        }

        return response()->json($movies);
    }

    public function getUpcoming($pageNum) {
        $dbController = new DatabaseController;
        $cinemaUpcoming = $dbController->getTitleOfUpcomingMovies(5*($pageNum-1), 5*$pageNum);
        if(!count($cinemaUpcoming)) {
            return response()->json([
                'error' => 'Semua film yang akan tayang sudah ditampilkan. Film yang kamu cari masih belum keluar di Indonesia kali ya?'
            ]);
        }

        $movies = [];
        foreach($cinemaUpcoming as $movie) {
            // Get title, genre, poster path, imdb id from omdb api
            $detailsResponse = $this->getUpcomingNowPlayingDetails($movie->name);
            $movie = json_decode($detailsResponse->getContent());
            array_push($movies, $movie);
        }

        return response()->json($movies);
    }

    public function getUpcomingNowPlayingDetails($name) {
        // Retrieve movies from database
        $dbController = new DatabaseController;
        $detailsDbResponse = $dbController->getDetailsByName($name);

        // Search movie to omdb
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $query = http_build_query([
            't'     => $name,
            'y'     => 2017
        ]);
        $omdbResponse = $client->request('GET', '?' . $query)->getBody();
        $detailsResponse = json_decode($omdbResponse);

        if(!$detailsDbResponse && ($detailsResponse->Response == 'False')) {
            return response()->json([
                'error' => 'Nama filmnya tidak ketemu. Coba film yang lainnya deh, aku pasti tau ;)'
            ]);
        }

        // Declare variables
        $dbId = $imdbId = $state = $title = null;
        $poster = $this->noImageUrl;
        $genre = '-';
        if($detailsDbResponse) {
            $dbId = $detailsDbResponse->id;
            $state = $detailsDbResponse->state;
            $title = ucwords(strtolower($detailsDbResponse->name));
            $poster = $detailsDbResponse->poster;
            $genre = ucwords(strtolower($detailsDbResponse->genre));
        }

        if($detailsResponse && $detailsResponse->Response != 'False') {
            $imdbId = $detailsResponse->imdbID;
            $title = $detailsResponse->Title;
            $poster = $detailsResponse->Poster != 'N/A' ? $detailsResponse->Poster : $this->noImageUrl;
            $genre = $detailsResponse->Genre;
        }

        return response()->json([
            'db_id'     => $dbId,
            'imdb_id'   => $imdbId,
            'state'     => $state,
            'title'     => $title,
            'poster'    => $poster,
            'genre'     => $genre
        ]);
    }

    public function getDetailsById($imdbId, $dbId, $state) {
        // Retrieve movie from database
        $detailsDbResponse = null;
        if ($dbId) {
            $dbController = new DatabaseController;
            $detailsDbResponse = $dbController->getDetailsById($dbId, $state);
        }

        // Search movie from omdb
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $omdbResponse = $client->request('GET', '', [
                'query' => ['i'         => $imdbId,
                            'tomatoes'  => 'true',
                            'plot'      => 'full'
                            ]
            ])->getBody();
        $detailsResponse = json_decode($omdbResponse);

        if(!$detailsDbResponse && ($detailsResponse->Response == 'False')) {
            return response()->json([
                'error' => 'Maaf informasi film belum ada untuk saat ini. Coba lagi ya lain waktu! :)'
            ]);
        }

        // Declare variables
        $title = $genre = $duration = $imdbRating = $plot = $video = null;
        $url = 'N/A';
        $ratings = [];

        if($detailsDbResponse) {
            $title = ucwords(strtolower($detailsDbResponse->name));
            $genre = ucwords(strtolower($detailsDbResponse->genre));
            $duration = $detailsDbResponse->duration;
            $plot = $detailsDbResponse->plot;
            $video = $detailsDbResponse->trailer;
        }

        if($detailsResponse && $detailsResponse->Response != 'False') {
            $title = $detailsResponse->Title;
            $genre = $detailsResponse->Genre;
            $duration = $detailsResponse->Runtime;
            $imdbRating = $detailsResponse->imdbRating;
            $ratings = $detailsResponse->Ratings;
            $plot = $detailsResponse->Plot;
            $url = $detailsResponse->Website;
        }

        return response()->json([
            'imdb_id'       => $imdbId,
            'db_id'         => $dbId,
            'state'         => $state,
            'title'         => $title,
            'genre'         => $genre,
            'duration'      => $duration,
            'imdb_rating'   => $imdbRating,
            'ratings'       => $ratings,
            'plot'          => $plot,
            'url'           => $url,
            'video'         => $video
        ]);
    }

    public function getDetailsByName($name) {
        // Retrieve movie from database
        $dbController = new DatabaseController;
        $detailsDbResponse = $dbController->getDetailsByName($name);

        // Search movie from omdb
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $query = http_build_query([
            't'     => $name
        ]);
        $omdbResponse = $client->request('GET', '?' . $query)->getBody();
        $detailsResponse = json_decode($omdbResponse);

        if(!$detailsDbResponse && ($detailsResponse->Response == 'False')) {
            return response()->json([
                'error' => 'Nama film tidak ditemukan. Apakah kamu yakin itu judul film yang benar?'
            ]);
        }

        // Declare variables
        $dbId = $imdbId = $state = $title = null;
        $poster = $this->noImageUrl;
        $genre = '-';
        if($detailsDbResponse) {
            $dbId = $detailsDbResponse->id;
            $state = $detailsDbResponse->state;
            $title = ucwords(strtolower($detailsDbResponse->name));
            $poster = $detailsDbResponse->poster;
            $genre = ucwords(strtolower($detailsDbResponse->genre));
        }

        if($detailsResponse && $detailsResponse->Response != 'False') {
            $imdbId = $detailsResponse->imdbID;
            $title = $detailsResponse->Title;
            $poster = $detailsResponse->Poster != 'N/A' ? $detailsResponse->Poster : $this->noImageUrl;
            $genre = $detailsResponse->Genre;
        }

        return response()->json([
            'db_id'     => $dbId,
            'imdb_id'   => $imdbId,
            'state'     => $state,
            'title'     => $title,
            'poster'    => $poster,
            'genre'     => $genre
        ]);
    }

    public function getReviews($movieId) {
        if(!$movieId) {
            return response()->json([
                'error' => 'Maaf tidak ada review untuk film ini :('
            ]);
        }

        // Get rating from omdb
        $client = new Client(['base_uri' => 'http://www.omdbapi.com']);
        $omdbResponse = $client->request('GET', '', [
                'query' => ['i'         => $movieId,
                            'tomatoes'  => 'true',
                            'plot'      => 'full'
                            ]
            ])->getBody();
        $ratingResponse = json_decode($omdbResponse);

        $imdbRating = null;
        $ratings = [];
        if ($ratingResponse && ($ratingResponse->Response != 'False')) {
            $imdbRating = $ratingResponse->imdbRating;
            $ratings = $ratingResponse->Ratings;
        }

        // Get reviews
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
        return response()->json([
            'imdb_rating'   => $imdbRating,
            'ratings'       => $ratings,
            'review'       => $reviews
        ]);
    }

    public function getCinema($dbId) {
        if(!$dbId) { // Avoid error on heroku
            return response()->json([
                'error' => 'Maaf, hari ini belum ada penayangan film ini. Nonton yang lain aja yuk!'
            ]);
        }

        $dbController = new DatabaseController;
        $cinema = $dbController->getCinema($dbId);
        if(!$cinema) {
            return response()->json([
                'error' => 'Maaf, hari ini belum ada penayangan film ini. Nonton yang lain aja yuk!'
            ]);
        }

        return response()->json($cinema);
    }

    public function getSchedule($dbId, $city) {
        $dbController = new DatabaseController;
        $schedule = $dbController->getSchedule($dbId, $city);
        if(!$schedule) {
            return response()->json([
                'error' => 'Maaf, hari ini filmnya ga tayang. Nonton yang lain aja yuk!'
            ]);
        }

        return response()->json($schedule);
    }
}

<?php

namespace App\Http\Controllers;

use App\Redis;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class LINEController extends Controller
{
    private $bot;
    private $katooPythonClient;

    public function __construct() {
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN', 'localhost'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_SECRET', 'localhost')]);
        $this->katooPythonClient = new \GuzzleHttp\Client(['headers' => ['Content-Type' => 'application/json']]);
    }
    
    public function index(Request $req) {
        $bot = $this->bot;
        $signature = $req->header(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            return response('Bad Request', 400);
        }

        // Check request with signature and parse request
        try {
            $events = $bot->parseEventRequest($req->getContent(), $signature);
        } catch (InvalidlidSignatureException $e) {
            return response('Invalid signature', 400);
        } catch (UnknownEventTypeException $e) {
            return response('Unknown event type has come', 400);
        } catch (UnknownMessageTypeException $e) {
            return response('Unknown message type has come', 400);
        } catch (InvalidEventRequestException $e) {
            return response('Invalid event request', 400);
        }

        foreach ($events as $event) {
            $sourceId = $event->getEventSourceId();
            $messages = [];

            if ($event instanceof MessageEvent) {
                if ($event instanceof TextMessage) {
                    $text = strtolower($event->getText());
                    switch ($text) {
                        case "tampilkan film yang sedang tayang":
                            $messages = $this->getNowPlayingMovies();
                            break;
                        case "tampilkan film yang akan tayang":
                            $messages = $this->getUpcomingMovies();
                            break;
                        case "cari film":
                            $messages = [new TextMessageBuilder("Ketikkan nama film yang ingin dicari")];
                            $redis = Redis::firstOrNew(['key' => 'source:' . $sourceId]);
                            $redis->value = 'movie:location';
                            $redis->save();
                            break;
                        case "tampilkan restoran terdekat":
                            $messages = [new TextMessageBuilder("Kirimkan lokasimu menggunakan fitur LINE location")];
                            break;
                        case "tampilkan restoran di suatu lokasi":
                            $messages = [new TextMessageBuilder("Ketikkan nama lokasi yang diinginkan")];
                            $redis = Redis::firstOrNew(['key' => 'source:' . $sourceId]);
                            $redis->value = 'resto:location';
                            $redis->save();
                            break;
                        case "cari restoran":
                            $messages = [new TextMessageBuilder("Ketikkan nama restoran yang ingin dicari")];
                            $redis = Redis::firstOrNew(['key' => 'source:' . $sourceId]);
                            $redis->value = 'resto:name';
                            $redis->save();
                            break;
                        default:
                            $stateRedis = Redis::where('key', 'source:' . $sourceId)->first();
                            if ($stateRedis) {
                                $stateValues = explode(':', $stateRedis->value);
                                $feature = $stateValues[0];
                                $featureMenu = $stateValues[1];

                                if($feature == 'movie') {
                                    switch ($featureMenu) {
                                        case 'location':
                                            $messages = $this->getMovieDetailsByName($text);
                                            $stateRedis->delete();
                                            break;
                                    }
                                } else if ($feature == 'resto') {
                                    switch ($featureMenu) {
                                        case 'location':
                                            $messages = $this->getRestaurantsByLocationQuery($text);
                                            $stateRedis->delete();
                                            break;
                                        case 'name':
                                            $messages = $this->getRestaurantsByQuery($text);
                                            $stateRedis->delete();
                                            break;
                                    }
                                }
                            } else {
                                $katooPythonResponseBody = $this->getKatooPythonResponse($text);
                                $katooPythonCode = $katooPythonResponseBody->reply->code;
                                switch ($katooPythonCode) {
                                    case 1: // Restaurant
                                        $location = $katooPythonResponseBody->reply->location;
                                        $restaurantName = $katooPythonResponseBody->reply->name;

                                        if ($restaurantName) {
                                            $messages = $this->getRestaurantsByQuery($restaurantName);
                                        } else if ($location) {
                                            $messages = $this->getRestaurantsByLocationQuery($location);
                                        } else {
                                            $messages = $this->getErrorMessage();
                                        }
                                        break;
                                    
                                    case 2: // Movie
                                        $movieName = $katooPythonResponseBody->reply->name;
                                        if($movieName) {
                                            $messages = $this->getMovieDetailsByName($movieName);
                                        } else {
                                            $messages = $this->getErrorMessage();
                                        }
                                        break;
                                    default: // Send text to chatterbot
                                        $messages = $this->getChatterBotReply($text);
                                        break;
                                }
                            }
                            break;
                    }
                } else if ($event instanceof LocationMessage) {
                    $messages = $this->getNearbyRestaurants($event->getLatitude(), $event->getLongitude(), 1);
                }
            } else if ($event instanceof PostbackEvent) {
                parse_str($event->getPostbackData(), $query);
                switch ($query['type']) {
                    case 'movie':
                        switch ($query['event']) {
                            case 'detail':
                                $imdbId = $query['imdb_id'];
                                $dbId = $query['db_id'];
                                $state = $query['state'];
                                $messages = $this->getMovieDetailsById($imdbId, $dbId, $state);
                                break;
                            case 'review':
                                $imdbId = $query['imdb_id'];
                                $messages = $this->getMovieReviews($imdbId);
                                break;
                            case 'cinema':
                                $imdbId = $query['imdb_id'];
                                $dbId = $query['db_id'];
                                $messages = $this->getMovieCinema($imdbId, $dbId);
                                break;
                            case 'schedule':
                                $imdbId = $query['imdb_id'];
                                $dbId = $query['db_id'];
                                $city = $query['city'];
                                $messages = $this->getMovieSchedule($imdbId, $dbId, $city);
                                break;
                            default:
                                # code...
                                break;
                        }
                        break;
                    case 'restaurant':
                        switch ($query['event']) {
                            case 'location':
                                $messages = $this->getLocation($query['lat'], $query['long'], $query['name'], $query['address']);
                                break;
                            case 'review':
                                $messages = $this->getRestaurantReviews($query['id']);
                                break;
                            case 'page':
                                switch ($query['feature']) {
                                    case 'nearby':
                                        $messages = $this->getNearbyRestaurants($query['lat'], $query['long'], $query['page']);
                                        break;
                                    case 'stop':
                                        $messages = $this->getStopNextPageMessage();
                                        break;
                                }
                                break;
                        }
                        break;
                }
            }

            foreach ($messages as $message) {
                $bot->pushMessage($sourceId, $message);
            }
        }

        return ('OK');
    }

    public function test() {
        // $messages = $this->getMovieDetailsById('tt2771200', 2, 'nowplaying');
        // $messages = $this->getMovieDetailsById('', '', '');
        // $messages = $this->getMovieReviews('tt0101414');
        // $messages = $this->getUpcomingMovies();
        // $messages = $this->getMovieDetailsByName('Logan');
        // $messages = $this->getMovieDetailsByName(' ');
        $messages = $this->getMovieCinema('tt2771200', '');
        foreach ($messages as $message) {
            $this->bot->pushMessage('U4927259e833db2ea3b9b8881c00cb786', $message); 
        }
    }

    public function getNowPlayingMovies() {
        $movieController = new MovieController;
        $response = $movieController->getNowPlaying(1); // TODO: call stored page in redis
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $movies = json_decode($response->getContent());

        $moviesTitle = [];
        $moviesCarouselColumns = [];
        foreach ($movies as $movie) {
            // Character limitation
            $title = $this->getLimitedText($movie->title, 40);
            $text = $this->getLimitedText($movie->genre, 60);
            $poster = strlen($movie->poster) < 1000 ? $movie->poster : 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg';
            array_push($moviesTitle, $title);

            // Buttons
            $templateAction = [
                new PostbackTemplateActionBuilder(
                    'Telusuri', 
                    'type=movie&event=detail&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=nowplaying'
                    // 'Telusuri ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Review & Rating',
                    'type=movie&event=review&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=nowplaying'
                    // 'Review & rating ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Info Penayangan',
                    'type=movie&event=cinema&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=nowplaying'
                    // 'Info penayangan ' . $title
                ),
            ];

            $movieCarouselColumn = new CarouselColumnTemplateBuilder($title, $text, $poster, $templateAction);
            array_push($moviesCarouselColumns, $movieCarouselColumn);
        }
        $moviesCarousel = new CarouselTemplateBuilder($moviesCarouselColumns);

        $altText = implode(', ', $moviesTitle);
        $altText = $this->getLimitedText($altText, 400);
        $moviesTemplateMessage = new TemplateMessageBuilder($altText, $moviesCarousel);
        return [$moviesTemplateMessage];
    }

    public function getUpcomingMovies() {
        $movieController = new MovieController;
        $response = $movieController->getUpcoming(1); // TODO: call stored page in redis
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $movies = json_decode($response->getContent());

        $moviesTitle = [];
        $moviesCarouselColumns = [];
        foreach ($movies as $movie) {
            // Character limitation
            $title = $this->getLimitedText($movie->title, 40);
            $text = $this->getLimitedText($movie->genre, 60);
            $poster = strlen($movie->poster) < 1000 ? $movie->poster : 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg';
            array_push($moviesTitle, $title);

            // Buttons
            $templateAction = [
                new PostbackTemplateActionBuilder(
                    'Telusuri', 
                    'type=movie&event=detail&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=upcoming'
                    // 'Telusuri ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Review & Rating',
                    'type=movie&event=review&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=upcoming'
                    // 'Review & rating ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Info Penayangan',
                    'type=movie&event=cinema&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=nowplaying'
                    // 'Info penayangan ' . $title
                ),
            ];

            $movieCarouselColumn = new CarouselColumnTemplateBuilder($title, $text, $poster, $templateAction);
            array_push($moviesCarouselColumns, $movieCarouselColumn);
        }

        $moviesCarousel = new CarouselTemplateBuilder($moviesCarouselColumns);
        $altText = implode(', ', $moviesTitle);
        $altText = $this->getLimitedText($altText, 400);
        $moviesTemplateMessage = new TemplateMessageBuilder($altText, $moviesCarousel);
        return [$moviesTemplateMessage];
    }

    public function getMovieDetailsById($imdbId, $dbId, $state) {
        $movieController = new MovieController;
        $response = $movieController->getDetailsById($imdbId, $dbId, $state);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }

        $movie = json_decode($response->getContent());
        if(isset($movie->error)) {
            $textMessages = new TextMessageBuilder($movie->error);
            return [$textMessages];
        }

        $identity = [
            'Judul: ' . $movie->title,
            'Genre: ' . $movie->genre,
            'Duration: ' . $movie->duration,
            'Rating: '
        ];

        $ratings = ['N/A'];
        if(isset($movie->imdb_rating)) {
            $ratings = ['- IMDB ' . $movie->imdb_rating . '/10'];
        }
        foreach ($movie->ratings as $rating) {
            array_push($ratings, '- ' . $rating->Source . ' ' . $rating->Value);
        }
        $text = implode("\n", $identity) . "\n" . implode("\n", $ratings);

        $textMessage = new TextMessageBuilder($text);
        return [$textMessage];
    }

    public function getMovieDetailsByName($movieName) {
        $movieController = new MovieController;
        $response = $movieController->getDetailsByName($movieName);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }

        $movie = json_decode($response->getContent());
        if(isset($movie->error)) {
            $textMessages = new TextMessageBuilder($movie->error);
            return [$textMessages];
        }

        // Character limitation
        $title = $this->getLimitedText($movie->title, 40);
        $text = $this->getLimitedText($movie->genre, 60);
        $poster = strlen($movie->poster) < 1000 ? $movie->poster : 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg';

        $templateAction = [
            new PostbackTemplateActionBuilder(
                'Telusuri', 
                'type=movie&event=detail&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=' . $movie->state
                // 'Telusuri ' . $title
            ),
            new PostbackTemplateActionBuilder(
                'Review & Rating',
                'type=movie&event=review&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=' . $movie->state
                // 'Review & rating ' . $title
            ),
            new PostbackTemplateActionBuilder(
                'Info Penayangan',
                'type=movie&event=cinema&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=' . $movie->state
                // 'Info penayangan ' . $title
            ),
        ]; 

        $buttonTemplate = new ButtonTemplateBuilder($title, $text, $poster, $templateAction);
        $altText = "Hasil pencarian " . $movieName;
        $templateMessage = new TemplateMessageBuilder($altText, $buttonTemplate);
        return [$templateMessage];
    }

    public function getMovieReviews($imdbId) {
        $movieController = new MovieController;
        $response = $movieController->getReviews($imdbId);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }

        $reviews = json_decode($response->getContent());
        if(isset($reviews->error)) {
            $textMessage = new TextMessageBuilder($reviews->error);
            return [$textMessage];
        }

        $review = $reviews[0];
        $text = $review->content . ' (' . $review->url. ')';
        $start = 0;
        $textMessages = [];
        while($start < strlen($text)) {
            $cutText = substr($text, $start, 2000);
            $textMessage = new TextMessageBuilder($cutText, null);
            array_push($textMessages, $textMessage);

            $start += 2000;
        }
        return $textMessages;
    }

    public function getMovieCinema($imdbId, $dbId) {
        $movieController = new MovieController;
        $response = $movieController->getCinema($dbId);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }

        $cinema = json_decode($response->getContent());
        if(isset($cinema->error)) {
            $textMessage = new TextMessageBuilder($cinema->error);
            return [$textMessage];
        }

        $templateAction = [];
        $end = sizeof($cinema) < 4 ? sizeof($cinema) : 4; // TODO: Looping until all cinemas has been send
        for ($i=0; $i<$end; $i++) { 
            array_push($templateAction, new PostbackTemplateActionBuilder(
                $cinema[$i],
                'type=movie&event=schedule&imdb_id=' . $imdbId . '&db_id=' . $dbId . '&city=' . $cinema[$i]
                // 'Jadwal penayangan di ' . $cinema[$i]
            ));
        }
        $buttonTemplate = new ButtonTemplateBuilder(null, 'Di mana kamu ingin menonton?', null, $templateAction);

        $altText = "Di mana kamu ingin menonton?\n" . implode(', ', $cinema);
        $templateMessage = new TemplateMessageBuilder($altText, $buttonTemplate);
        return [$templateMessage];
    }

    public function getMovieSchedule($imdbId, $dbId, $city) {
        $movieController = new MovieController;
        $response = $movieController->getSchedule($dbId, $city);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }

        $schedules = json_decode($response->getContent());
        if(isset($schedules->error)) {
            $textMessages = new TextMessageBuilder($schedules->error);
            return [$textMessages];
        }

        $text = "JADWAL HARI INI\n";
        foreach ($schedules as $cinema => $schedule) {
            $text .= "\n" . $cinema . "\n";
            foreach ($schedule as $sch) {
                $schText = $sch->auditype . ' (Rp' . $sch->price . ') - ' . $sch->showtime . "\n";
                $text .= $schText;
            }
            if(empty($schedule)) {
                $text .= 'Tidak ada jadwal pada bioskop ini.';
            }
        }
        
        $textMessages = new TextMessageBuilder($text);
        return [$textMessages];
    }

    private function getLocation($lat, $long, $name, $address) {
        $locationMessageBuilders = [new LocationMessageBuilder(
            $name,
            $address,
            $lat,
            $long
        )];
        return $locationMessageBuilders;
    }

    private function getRestaurantReviews($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getReviews($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $reviews = json_decode($response->getContent());

        $textMessageBuilders = [];
        foreach ($reviews->user_reviews as $review) {
            $reviewMessage = $review->rating . '/5';
            $reviewMessage .= "\n" . $review->review_text;
            $textMessageBuilders[] = new TextMessageBuilder($reviewMessage);
        }

        if (empty($textMessageBuilders)) {
            return $this->getEmptyReviewsMessage();
        }
        return $textMessageBuilders;
    }

    private function getNearbyRestaurants($lat, $long, $page) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getNearby($lat, $long);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $restaurants = json_decode($response->getContent());

        $carouselColumnTemplateBuilders = [];
        $numRestaurants = sizeof($restaurants->nearby_restaurants);
        for ($i = 0; $i < 5; $i++) {
            $index = $i + ($page - 1) * 5;
            if ($index >= $numRestaurants) {
                break;
            }
            $restaurant = $restaurants->nearby_restaurants[$index];

            $templateActionBuilders = [
                new PostbackTemplateActionBuilder(
                    'Lokasi',
                    'type=restaurant&event=location&name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
                ),
                new UriTemplateActionBuilder('Menu', $restaurant->menu_url),
                new PostbackTemplateActionBuilder(
                    'Ulasan',
                    'type=restaurant&event=review&id=' . $restaurant->id
                )
            ];

            $aggregateRating = $restaurant->aggregate_rating;
            if ($restaurant->aggregate_rating != 'Not rated') {
                $aggregateRating .= '/5';
            }
            $name = $restaurant->name;
            if (strlen($name) > 40) {
                $name = substr($name, 0, 37) . '...';
            }
            $address = $restaurant->address;
            $addressMaxLength = 60 - strlen($aggregateRating . "\n");
            if (strlen($address) > $addressMaxLength) {
                $address = substr($address, 0, $addressMaxLength - 3) . '...';
            }
            $featuredImage = $restaurant->featured_image;
            if ($featuredImage == "") {
                $featuredImage = 'https://b.zmtcdn.com/images/photoback.png';
            }

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $featuredImage,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
        }

        if (sizeof($carouselColumnTemplateBuilders) > 0) {
            $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
            $templateMessageBuilders = [new TemplateMessageBuilder('Restoran Terdekat', $carouselTemplateBuilder)];
        } else {
            return $this->getEmptyRestaurantsMessage();
        }

        if ($numRestaurants > ($page * 5)) {
            $templateActionBuilders = [new PostbackTemplateActionBuilder(
                'Ya',
                'type=restaurant&event=page&feature=nearby&page=' . ($page + 1) . '&lat=' . $lat . '&long=' . $long
            )];
            $templateActionBuilders[] = new PostbackTemplateActionBuilder(
                'Tidak',
                'type=restaurant&event=page&feature=stop'
            );
            $confirmTemplateBuilder = new ConfirmTemplateBuilder('Halaman selanjutnya?', $templateActionBuilders);
            $templateMessageBuilders[] = new TemplateMessageBuilder('Halaman selanjutnya?', $confirmTemplateBuilder);
        }

        return $templateMessageBuilders;
    }

    private function getRestaurantsByLocationQuery($query) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getByLocationQuery($query);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $restaurants = json_decode($response->getContent());

        $carouselColumnTemplateBuilders = [];
        $templateMessageBuilders = [];
        foreach ($restaurants->restaurants as $restaurant) {
            $templateActionBuilders = [
                new PostbackTemplateActionBuilder(
                    'Lokasi',
                    'type=restaurant&event=location&name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
                ),
                new UriTemplateActionBuilder('Menu', $restaurant->menu_url),
                new PostbackTemplateActionBuilder(
                    'Ulasan',
                    'type=restaurant&event=review&id=' . $restaurant->id
                )
            ];

            $aggregateRating = $restaurant->aggregate_rating;
            if ($restaurant->aggregate_rating != 'Not rated') {
                $aggregateRating .= '/5';
            }
            $name = $restaurant->name;
            if (strlen($name) > 40) {
                $name = substr($name, 0, 37) . '...';
            }
            $address = $restaurant->address;
            $addressMaxLength = 60 - strlen($aggregateRating . "\n");
            if (strlen($address) > $addressMaxLength) {
                $address = substr($address, 0, $addressMaxLength - 3) . '...';
            }
            $featuredImage = $restaurant->featured_image;
            if ($featuredImage == "") {
                $featuredImage = 'https://b.zmtcdn.com/images/photoback.png';
            }

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $featuredImage,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
            if (sizeof($carouselColumnTemplateBuilders) == 5) {
                $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
                $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran di ' . $query, $carouselTemplateBuilder);
                $carouselColumnTemplateBuilders = [];
            }
        }

        if (sizeof($carouselColumnTemplateBuilders) > 0 && sizeof($carouselColumnTemplateBuilders) < 5) {
            $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
            $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran di ' . $query, $carouselTemplateBuilder);
        }

        if (empty($templateMessageBuilders)) {
            return $this->getEmptyRestaurantsMessage();
        }
        return $templateMessageBuilders;
    }

    private function getRestaurantsByQuery($query) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getByQuery($query);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $restaurants = json_decode($response->getContent());

        $carouselColumnTemplateBuilders = [];
        $templateMessageBuilders = [];
        foreach ($restaurants->restaurants as $restaurant) {
            $templateActionBuilders = [
                new PostbackTemplateActionBuilder(
                    'Lokasi',
                    'type=restaurant&event=location&name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
                ),
                new UriTemplateActionBuilder('Menu', $restaurant->menu_url),
                new PostbackTemplateActionBuilder(
                    'Ulasan',
                    'type=restaurant&event=review&id=' . $restaurant->id
                )
            ];

            $aggregateRating = $restaurant->aggregate_rating;
            if ($restaurant->aggregate_rating != 'Not rated') {
                $aggregateRating .= '/5';
            }
            $name = $restaurant->name;
            if (strlen($name) > 40) {
                $name = substr($name, 0, 37) . '...';
            }
            $address = $restaurant->address;
            $addressMaxLength = 60 - strlen($aggregateRating . "\n");
            if (strlen($address) > $addressMaxLength) {
                $address = substr($address, 0, $addressMaxLength - 3) . '...';
            }
            $featuredImage = $restaurant->featured_image;
            if ($featuredImage == "") {
                $featuredImage = 'https://b.zmtcdn.com/images/photoback.png';
            }

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $featuredImage,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
            if (sizeof($carouselColumnTemplateBuilders) == 5) {
                $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
                $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran ' . $query, $carouselTemplateBuilder);
                $carouselColumnTemplateBuilders = [];
            }
        }

        if (sizeof($carouselColumnTemplateBuilders) > 0 && sizeof($carouselColumnTemplateBuilders) < 5) {
            $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
            $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran ' . $query, $carouselTemplateBuilder);
        }

        if (empty($templateMessageBuilders)) {
            return $this->getEmptyRestaurantsMessage();
        }
        return $templateMessageBuilders;
    }

    private function getKatooPythonResponse($text) {
        $requestBody = ['message' => $text];
        $response = $this->katooPythonClient->request('POST', 'https://katoo-python.herokuapp.com/get-reply', ['json' => $requestBody]);
        if ($response->getStatusCode() != 200) {
            return $this->getErrorMessage();
        }

        $responseBody = json_decode($response->getBody());
        return $responseBody;
    }

    private function getChatterBotReply($text) {
        $requestBody = ['message' => $text];
        $response = $this->katooPythonClient->request('POST', 'http://katoo.pythonanywhere.com/get-reply', ['json' => $requestBody]);
        if ($response->getStatusCode() != 200) {
            return $this->getErrorMessage();
        }

        $responseBody = json_decode($response->getBody());
        return [new TextMessageBuilder($responseBody->reply)];
    }

    private function getErrorMessage() {
        $errorMessageBuilders = [new TextMessageBuilder('Mohon maaf Katoo sedang lelah, silahkan coba beberapa saat lagi :)')];
        return $errorMessageBuilders;
    }

    private function getEmptyRestaurantsMessage() {
        $emptyRestaurantMessageBuilders = [new TextMessageBuilder('Maaf restoran tidak ditemukan :(')];
        return $emptyRestaurantMessageBuilders;
    }

    private function getEmptyReviewsMessage() {
        $emptyReviewsMessageBuilders = [new TextMessageBuilder('Maaf ulasan tidak ada :(')];
        return $emptyReviewsMessageBuilders;
    }

    private function getStopNextPageMessage() {
        $message = [new TextMessageBuilder("Oke :D\nSilahkan pilih fitur lain yang diinginkan")];
        return $message;
    }

    private function getLimitedText($text, $limit) {
        if (strlen($text) > $limit) {
            return substr($text, 0, $limit-3) . '...';
        }
        return $text;
    }
}

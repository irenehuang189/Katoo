<?php

namespace App\Http\Controllers;

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
                    $text = $event->getText();
                    if (strtolower($text) == "tampilkan film yang sedang tayang") {
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "tampilkan film yang akan tayang") {
                        $messages = $this->getUpcomingMovies();
                    } else if (strtolower($text) == "cari film") {
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "tampilkan restoran terdekat") {
                        $messages = [new TextMessageBuilder("Kirimkan lokasimu menggunakan fitur LINE location")];
                    } else if (strtolower($text) == "tampilkan restoran di suatu lokasi") {
                        $messages = [new TextMessageBuilder("Ketikkan nama lokasi yang diinginkan")];
                    } else if (strtolower($text) == "cari restoran") {
                        $messages = [new TextMessageBuilder("Cari restoran dengan mengetikkan:\nmakan di <nama restoran>\n\nContoh:\nmakan di mcd")];
                    } else {
                        $katooPythonResponseBody = $this->getKatooPythonResponse($text);
                        $katooPythonCode = $katooPythonResponseBody->reply->code;
                        if ($katooPythonCode == 1) {
                            $location = $katooPythonResponseBody->reply->location;
                            $restaurantName = $katooPythonResponseBody->reply->name;

                            if ($restaurantName) {
                                $messages = $this->getRestaurantsByQuery($restaurantName);
                            } else if ($location) {
                                $messages = $this->getRestaurantsByLocationQuery($location);
                            } else {
                                $messages = $this->getErrorMessage();
                            }
                        } else if ($katooPythonCode == 2) {
                            $messages = [new TextMessageBuilder($text)];
                        } else {
                            $messages = $this->getChatterBotReply($text);
                        }
                    }
                } else if ($event instanceof LocationMessage) {
                    $messages = $this->getNearbyRestaurants($event->getLatitude(), $event->getLongitude());
                }
            } else if ($event instanceof PostbackEvent) {
                parse_str($event->getPostbackData(), $query);
                if($query['type'] == 'movie') {
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
                        default:
                            # code...
                            break;
                    }
                } else if ($query['type'] == 'restaurant') {
                    if ($query['event'] == 'location') {
                        $messages = $this->getLocation($query['lat'], $query['long'], $query['name'], $query['address']);
                    } else if ($query['event'] == 'review') {
                        $messages = $this->getRestaurantReviews($query['id']);
                    }
                }
            }

            foreach ($messages as $message) {
                $bot->pushMessage($sourceId, $message);
            }
        }

        return ('OK');
    }

    public function test() {
        $messages = $this->getMovieSchedule('tt2771200', 2, 'Bandung');
        foreach ($messages as $message) {
            $this->bot->pushMessage('U4927259e833db2ea3b9b8881c00cb786', $message); 
        }
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
        $end = sizeof($movies) < 5 ? sizeof($movies) : 5;
        for ($i=0; $i<$end; $i++) { 
            $movie = $movies[$i];

            // Character limitation
            $title = $this->getLimitedText($movie->title, 40);
            $text = $this->getLimitedText($movie->genre, 60);
            $poster = strlen($movie->poster) < 1000 ? $movie->poster : 'https://pbs.twimg.com/profile_images/600060188872155136/st4Sp6Aw.jpg';
            array_push($moviesTitle, $title);

            // Buttons
            $templateAction = [
                new PostbackTemplateActionBuilder(
                    'Telusuri', 
                    'type=movie&event=detail&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=upcoming',
                    'Telusuri ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Review & Rating',
                    'type=movie&event=review&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id . '&state=upcoming',
                    'Review & rating ' . $title
                ),
                new PostbackTemplateActionBuilder(
                    'Info Penayangan',
                    'type=movie&event=schedule&imdb_id=' . $movie->imdb_id . '&db_id=' . $movie->db_id,
                    'Info penayangan ' . $title
                ),
            ];

            $movieCarouselColumn = new CarouselColumnTemplateBuilder($title, $text, $poster, $templateAction);
            array_push($moviesCarouselColumns, $movieCarouselColumn);
        }

        $moviesCarousel = new CarouselTemplateBuilder($moviesCarouselColumns);
        $altText = implode(', ', $moviesTitle);
        $moviesTemplateMessage = new TemplateMessageBuilder($altText, $moviesCarousel);
        return [$moviesTemplateMessage];
    }

    public function getMovie() {
        $movieController = new MovieController;
        $response = $movieController->getDetails(293167);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $movie = json_decode($response->getContent());

        // Button
        $templateActionButton = [
            new MessageTemplateActionBuilder('Lihat Sinopsis', $movie->plot),
            new UriTemplateActionBuilder('Telusuri lebih lanjut', $movie->url)
        ];

        $text = [
            'IMDB Score: ' . $movie->imdb_rating,
            'Genre: ' . $movie->genre,
            'Duration: ' . $movie->duration,
        ];
        $movieButtonTemplate = new ButtonTemplateBuilder($movie->title, implode('\n', $text), $movie->poster_path, $templateActionButton);

        $altText = 'Rincian film ' . $movie->title;
        $movieTemplateMessage = new TemplateMessageBuilder($altText, $movieButtonTemplate);
        return $movieTemplateMessage;
    }

    public function getMovieDetailsById($imdbId, $dbId, $state) {
        $movieController = new MovieController;
        $response = $movieController->getDetailsById($imdbId, $dbId, $state);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $movie = json_decode($response->getContent());

        $identity = [
            'Judul: ' . $movie->title,
            'Genre: ' . $movie->genre,
            'Duration: ' . $movie->duration,
            'Rating: '
        ];
        $ratings = ['- IMDB ' . $movie->imdb_rating . '/10'];
        foreach ($movie->ratings as $rating) {
            array_push($ratings, '- ' . $rating->Source . ' ' . $rating->Value);
        }
        $text = implode("\n", $identity) . "\n" . implode("\n", $ratings);

        $textMessage = new TextMessageBuilder($text);
        return [$textMessage];
    }

    public function getMovieReviews($imdbId) {
        $movieController = new MovieController;
        $response = $movieController->getReviews($imdbId);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $reviews = json_decode($response->getContent());

        $review = $reviews[0];
        if(!$review->id) {
            $textMessage = new TextMessageBuilder($review->content);
            return [$textMessage];
        }

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

        $templateAction = [];
        $end = sizeof($cinema) < 4 ? sizeof($cinema) : 4; // TODO: Looping until all cinemas has been send
        for ($i=0; $i<$end; $i++) { 
            array_push($templateAction, new PostbackTemplateActionBuilder(
                $cinema[$i],
                'type=movie&event=schedule&imdb_id=' . $imdbId . '&db_id=' . $dbId . '&city=' . $cinema[$i],
                'Jadwal penayangan di ' . $cinema[$i]
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
            $text .= $cinema . "\n";
            foreach ($schedule as $sch) {
                $schText = $sch->auditype . ' (Rp' . $sch->price . ') - ' . $sch->showtime . "\n";
                $text .= $schText;
            }
            if(empty($schedule)) {
                $text .= 'Tidak ada jadwal pada bioskop ini.';
            }
            $text .= "\n";
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

    private function getNearbyRestaurants($lat, $long) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getNearby($lat, $long);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $restaurants = json_decode($response->getContent());

        $carouselColumnTemplateBuilders = [];
        $templateMessageBuilders = [];
        foreach ($restaurants->nearby_restaurants as $restaurant) {
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
                $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran Terdekat', $carouselTemplateBuilder);
                $carouselColumnTemplateBuilders = [];
            }
        }

        if (sizeof($carouselColumnTemplateBuilders) > 0 && sizeof($carouselColumnTemplateBuilders) < 5) {
            $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
            $templateMessageBuilders[] = new TemplateMessageBuilder('Restoran Terdekat', $carouselTemplateBuilder);
        }

        if (empty($templateMessageBuilders)) {
            return $this->getEmptyRestaurantsMessage();
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

    private function getLimitedText($text, $limit) {
        if (strlen($text) > $limit) {
            return substr($text, 0, $limit-3) . '...';
        }
        return $text;
    }
}

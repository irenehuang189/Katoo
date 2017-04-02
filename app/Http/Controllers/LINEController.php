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

    public function __construct() {
        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN', 'localhost'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_SECRET', 'localhost')]);
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
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "cari film") {
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "tampilkan restoran terdekat") {
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "tampilkan restoran di suatu lokasi") {
                        $messages = [new TextMessageBuilder($text)];
                    } else if (strtolower($text) == "cari restoran") {
                        $messages = [new TextMessageBuilder($text)];
                    } else {
                        $messages = [new TextMessageBuilder($text)];
                    }
                } else if ($event instanceof LocationMessage) {
                    $messages = $this->getNearbyRestaurants($event->getLatitude(), $event->getLongitude());
                }
            } else if ($event instanceof PostbackEvent) {
                parse_str($event->getPostbackData(), $query);
                if ($query['type'] == 'restaurant') {
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
        $message = $this->getRestaurant(18387851);
        $this->bot->pushMessage('U4927259e833db2ea3b9b8881c00cb786', $message);
    }

    public function getPopularMovies() {
        $movieController = new MovieController;
        $response = $movieController->getPopular(1); // TODO: call stored page in redis
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $movies = json_decode($response->getContent());

        $moviesTitle = [];
        $moviesCarouselColumns = [];
        foreach($movies as $movie) {
            array_push($moviesTitle, $movie->title);
            $genreText = 'Genre: ' . implode(', ', $movie->genre) . '\n';
            $ratingText = 'TMDB Score: ' . $movie->tmdb_rating . '/10.0';
            $text = $genreText . $ratingText;
            $movieCarouselColumn = new CarouselColumnTemplateBuilder($movie->title, $text, $movie->poster_path, []);
            array_push($moviesCarouselColumns, $movieCarouselColumn);
        }

        $moviesCarousel = new CarouselTemplateBuilder($moviesCarouselColumns);
        $altText = implode(', ', $moviesTitle);
        $moviesTemplateMessage = new TemplateMessageBuilder($altText, $moviesCarousel);
        return $moviesTemplateMessage;
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
        foreach($movies as $movie) {
            array_push($moviesTitle, $movie->title);
            $genreText = 'Genre: ' . implode(', ', $movie->genre) . '\n';
            $ratingText = 'TMDB Score: ' . $movie->tmdb_rating . '/10.0';
            $text = $genreText . $ratingText;
            $movieCarouselColumn = new CarouselColumnTemplateBuilder($movie->title, $text, $movie->poster_path, []);
            array_push($moviesCarouselColumns, $movieCarouselColumn);
        }

        $moviesCarousel = new CarouselTemplateBuilder($moviesCarouselColumns);
        $altText = implode(', ', $moviesTitle);
        $moviesTemplateMessage = new TemplateMessageBuilder($altText, $moviesCarousel);
        return $moviesTemplateMessage;
    }

    public function getMovieDetails() {
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

    public function getMovieReviews() {
        $movieController = new MovieController;
        $response = $movieController->getReviews(293167);
        if($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $reviews = json_decode($response->getContent());

        $text = [];
        foreach($reviews as $review) {
            array_push($text, $review->content . ' (' . $review->url. ')');
        }
        return new TextMessageBuilder(implode('\n\n', $text));
    }

    /* Restaurant */
    private function getRestaurant($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->get($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $restaurant = json_decode($response->getContent());

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
            $featuredImage = null;
        }

        $buttonTemplateBuilder = new ButtonTemplateBuilder(
            $name,
            $aggregateRating . "\n" . $address,
            $featuredImage,
            $templateActionBuilders
        );

        $templateMessageBuilders = [new TemplateMessageBuilder($restaurant->name, $buttonTemplateBuilder)];
        return $templateMessageBuilders;
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

    private function getRestaurantRating($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getRating($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $rating = json_decode($response->getContent());

        $aggregateRating = $rating->aggregate_rating;
        if ($aggregateRating != 'Not rated') {
            $aggregateRating = $aggregateRating . '/5';
        }
        $textMessageBuilders = [new TextMessageBuilder($aggregateRating)];
        return $textMessageBuilders;
    }

    private function getRestaurantMenu($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getMenu($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $menu = json_decode($response->getContent());

        $textMessageBuilders = [new TextMessageBuilder($menu->menu_url)];
        return $textMessageBuilders;
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
        foreach ($restaurants->nearby_restaurants as $restaurant) {
            $templateActionBuilders = [
                new PostbackTemplateActionBuilder(
                    'Lokasi',
                    'name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
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
                $featuredImage = null;
            }

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $featuredImage,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
            if (sizeof($carouselColumnTemplateBuilders) == 5) {
                break;
            }
        }

        $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
        $templateMessageBuilders = [new TemplateMessageBuilder('Restoran Terdekat', $carouselTemplateBuilder)];
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
        foreach ($restaurants->restaurants as $restaurant) {
            $templateActionBuilders = [
                new PostbackTemplateActionBuilder(
                    'Lokasi',
                    'name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
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
                $featuredImage = null;
            }

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $featuredImage,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
            if (sizeof($carouselColumnTemplateBuilders) == 5) {
                break;
            }
        }

        $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
        $templateMessageBuilders = [new TemplateMessageBuilder('Restoran di ' . $query, $carouselTemplateBuilder)];
        return $templateMessageBuilders;
    }

    private function getErrorMessage() {
        $errorMessageBuilders = [new TextMessageBuilder('Mohon maaf Katoo sedang lelah, silahkan coba beberapa saat lagi :)')];
        return $errorMessageBuilders;
    }
}

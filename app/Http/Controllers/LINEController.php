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
            $message = "";

            if ($event instanceof MessageEvent) {
                if ($event instanceof TextMessage) {
                    $replyText = $event->getText();
                    $message = new TextMessageBuilder($replyText);
                } else if ($event instanceof LocationMessage) {
                    $message = $this->getNearbyRestaurant($event->getLatitude(), $event->getLongitude());
                }
            } else if ($event instanceof PostbackEvent) {
                parse_str($event->getPostbackData(), $query);
                $locationKeys = ['lat', 'long', 'name', 'address'];

                if (count(array_intersect_key(array_flip($locationKeys), $query)) === count($locationKeys)) {
                    $message = $this->getLocation($query['lat'], $query['long'], $query['name'], $query['address']);
                }
            }

            if ($message instanceof MessageBuilder) {
                $bot->pushMessage($sourceId, $message);
            }
        }

        return ('OK');
    }

    public function test() {
        $message = $this->getErrorMessage();
        $this->bot->pushMessage('U4927259e833db2ea3b9b8881c00cb786', $message);
    }

    public function getPopularMovie() {
        $movieController = new MovieController;
        $response = $movieController->getPopular(1);
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
                'name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
            ),
            new UriTemplateActionBuilder('Menu', $restaurant->menu_url),
            new UriTemplateActionBuilder('Lihat di Zomato', $restaurant->url)
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

        $buttonTemplateBuilder = new ButtonTemplateBuilder(
            $name,
            $aggregateRating . "\n" . $address,
            $restaurant->featured_image,
            $templateActionBuilders
        );

        $templateMessageBuilder = new TemplateMessageBuilder($restaurant->name, $buttonTemplateBuilder);
        return $templateMessageBuilder;
    }

    private function getLocation($lat, $long, $name, $address) {
        $locationMessageBuilder = new LocationMessageBuilder(
            $name,
            $address,
            $lat,
            $long
        );
        return $locationMessageBuilder;
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
        $textMessageBuilder = new TextMessageBuilder($aggregateRating);
        return $textMessageBuilder;
    }

    private function getRestaurantMenu($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getMenu($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $menu = json_decode($response->getContent());

        $textMessageBuilder = new TextMessageBuilder($menu->menu_url);
        return $textMessageBuilder;
    }

    private function getRestaurantReviews($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->getReviews($id);
        if ($response->status() != 200) {
            return $this->getErrorMessage();
        }
        $reviews = json_decode($response->getContent());

        $reviewsMessage = '';
        foreach ($reviews->user_reviews as $review) {
            $reviewsMessage .= "\n\n" . $review->rating . '/5';
            $reviewsMessage .= "\n" . $review->review_text;
        }
        $textMessageBuilder = new TextMessageBuilder($reviewsMessage);
        return $textMessageBuilder;
    }

    private function getNearbyRestaurant($lat, $long) {
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
                new UriTemplateActionBuilder('Lihat di Zomato', $restaurant->url)
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

            $carouselColumnTemplateBuilder = new CarouselColumnTemplateBuilder(
                $name,
                $aggregateRating . "\n" . $address,
                $restaurant->featured_image,
                $templateActionBuilders
            );

            $carouselColumnTemplateBuilders[] = $carouselColumnTemplateBuilder;
            if (sizeof($carouselColumnTemplateBuilders) == 5) {
                break;
            }
        }

        $carouselTemplateBuilder = new CarouselTemplateBuilder($carouselColumnTemplateBuilders);
        $templateMessageBuilder = new TemplateMessageBuilder('Restaurant Terdekat', $carouselTemplateBuilder);
        return $templateMessageBuilder;
    }

    private function getErrorMessage() {
        $errorMessageBuilder = new TextMessageBuilder('Mohon maaf Katoo sedang lelah, silahkan coba beberapa saat lagi :)');
        return $errorMessageBuilder;
    }
}

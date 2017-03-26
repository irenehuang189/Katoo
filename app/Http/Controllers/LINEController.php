<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
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

    public function getPopular() {

    }

    /* Restaurant */
    private function getRestaurant($id) {
        $restaurantController = new RestaurantController;
        $response = $restaurantController->get($id);
        if ($response->status() != 200) {
            return response($response->getContent(), $response->status());
        }
        $restaurant = json_decode($response->getContent());

        $templateActionBuilders = [
            new PostbackTemplateActionBuilder(
                'Location',
                'name=' . $restaurant->name . '&address=' . $restaurant->address . '&lat=' . $restaurant->latitude . '&long=' . $restaurant->longitude
            ),
            new UriTemplateActionBuilder('Menu', $restaurant->menu_url),
            new UriTemplateActionBuilder('View in Zomato', $restaurant->url)
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
}

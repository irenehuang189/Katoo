<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException; 

class LINEController extends Controller
{
    private $bot;

    public function __construct() {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('LINE_ACCESS_TOKEN', 'localhost'));
        $this->bot = new \LINE\LINEBot($httpClient, ['channelSecret' => env('LINE_SECRET', 'localhost')]);
    }
    
    public function index(Request $req) {
        // echo $req;
        // $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
        // $response = $this->bot->replyMessage('<replyToken>', $textMessageBuilder);

        // echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
        $bot = $this->bot;
        $signature = $req->header(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            return response('Bad Request', 400);
        }

        // Check request with signature and parse request
        try {
            // echo $req->getContent() . env('LINE_SECRET', 'localhost') . '++' . $signature;
            // return;
            $events = $bot->parseEventRequest($req->getContent(), $signature);
        } catch (InvalidlidSignatureException $e) {
            // return $e->getMessage();
            return response('Invalid signature', 400);
        } catch (UnknownEventTypeException $e) {
            return response('Unknown event type has come', 400);
        } catch (UnknownMessageTypeException $e) {
            return response('Unknown message type has come', 400);
        } catch (InvalidEventRequestException $e) {
            return response('Invalid event request', 400);
        }

        foreach ($events as $event) {
            if (!($event instanceof MessageEvent)) {
                echo ('Non message event has come');
                continue;
            }

            if (!($event instanceof TextMessage)) {
                echo ('Non text message has come');
                continue;
            }

            $replyText = $event->getText();
            echo ('Reply text: ' . $replyText);
            $resp = $bot->replyText($event->getReplyToken(), $replyText);
            echo ($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
        }

        $res->write('OK');
        return $res;
    }

    public function getPopular() {

    }
}

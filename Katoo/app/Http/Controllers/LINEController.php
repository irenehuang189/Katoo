<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LINEController extends Controller
{
    public function __construct() {
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(env('LINE_ACCESS_TOKEN', 'localhost'));
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => env('LINE_SECRET', 'localhost')]);
    }
    
    public function index(Request $request) {
        print_r($request);
        // $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
        // $response = $bot->replyMessage('<replyToken>', $textMessageBuilder);

        // echo $response->getHTTPStatus() . ' ' . $response->getRawBody();   
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    private $client;
	private $defaultOption;

    public function __construct() {
    	$this->client = new \GuzzleHttp\Client(['headers' => ['Accept' => 'application/json', 'user-key' => env('ZOMATO_API_KEY')]]);
    	$this->defaultOption = ['verify' => false];
    }

    public function getNearby(Request $request) {
        $lat = $request->query('lat');
        $long = $request->query('long');
        if (!$lat || !$long) {
            return response('', 400);
        }

        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/geocode?lat=' . $lat . '&lon=' . $long, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $restaurants = $responseBody->nearby_restaurants;
    	return $restaurants;
    }
}

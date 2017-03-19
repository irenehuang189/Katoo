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
    	return response()->json($restaurants);
    }

    private function getLocation($keyword) {
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/locations?query=' . $keyword, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $locations = $responseBody->location_suggestions;
        if ($locations) {
            return response()->json($locations[0]);
        } else {
            return response('', 400);
        }
    }

    private function getByLocation($locationId, $locationType) {
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/search?entity_id=' . $locationId . '&entity_type=' . $locationType, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $restaurants = $responseBody->restaurants;
        return response()->json($restaurants);
    }
}

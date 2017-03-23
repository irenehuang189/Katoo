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

    public function get($id) {
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/restaurant?res_id=' . $id, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        if ($responseBody->R->res_id > 0) {
            if ($responseBody->user_rating->rating_text == "Not rated") {
                $aggregate_rating = $responseBody->user_rating->rating_text;
            } else {
                $aggregate_rating = $responseBody->user_rating->aggregate_rating;
            }

            $restaurant = [
                'id' => $id,
                'name' => $responseBody->name,
                'url' => $responseBody->url,
                'address' => $responseBody->location->address,
                'latitude' => $responseBody->location->latitude,
                'longitude' => $responseBody->location->longitude,
                'aggregate_rating' => $aggregate_rating,
                'menu_url' => $responseBody->menu_url,
                'featured_image' => $responseBody->featured_image
            ];
            return response()->json($restaurant);
        }
        return response('Invalid restaurant ID', 400);
    }

    public function getRating($id) {
        $response = $this->get($id);
        if ($response->status() != 200) {
            return response($response->getContent(), $response->status());
        }

        $restaurant = json_decode($response->getContent());
        return response()->json(["aggregate_rating" => $restaurant->aggregate_rating]);
    }

    public function getMenu($id) {
        $response = $this->get($id);
        if ($response->status() != 200) {
            return response($response->getContent(), $response->status());
        }

        $restaurant = json_decode($response->getContent());
        return response()->json(["menu_url" => $restaurant->menu_url]);
    }

    public function getReviews($id) {
        $response = $this->get($id);
        if ($response->status() != 200) {
            return response($response->getContent(), $response->status());
        }

        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/reviews?res_id=' . $id, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $reviews = [];
        foreach ($responseBody->user_reviews as $userReview) {
            $review = [
                'rating' => $userReview->review->rating,
                'review_text' => $userReview->review->review_text
            ];
            $reviews[] = $review;
        }

        return response()->json(["user_reviews" => $reviews]);
    }

    public function getNearby(Request $request) {
        $lat = $request->query('lat');
        $long = $request->query('long');
        if (!$lat || !$long) {
            return response('No latitude or longitude', 400);
        }

        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/geocode?lat=' . $lat . '&lon=' . $long, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $restaurants = [];
        foreach ($responseBody->nearby_restaurants as $nearbyRestaurant) {
            if ($nearbyRestaurant->restaurant->user_rating->rating_text == "Not rated") {
                $aggregate_rating = $nearbyRestaurant->restaurant->user_rating->rating_text;
            } else {
                $aggregate_rating = $nearbyRestaurant->restaurant->user_rating->aggregate_rating;
            }

            $restaurant = [
                'id' => $nearbyRestaurant->restaurant->R->res_id,
                'name' => $nearbyRestaurant->restaurant->name,
                'url' => $nearbyRestaurant->restaurant->url,
                'address' => $nearbyRestaurant->restaurant->location->address,
                'latitude' => $nearbyRestaurant->restaurant->location->latitude,
                'longitude' => $nearbyRestaurant->restaurant->location->longitude,
                'aggregate_rating' => $aggregate_rating,
                'menu_url' => $nearbyRestaurant->restaurant->menu_url,
                'featured_image' => $nearbyRestaurant->restaurant->featured_image
            ];
            $restaurants[] = $restaurant;
        }
    	return response()->json(["nearby_restaurants" => $restaurants]);
    }

    public function getByLocationQuery(Request $request) {
        $query = $request->query('query');
        if (!$query) {
            return response('No location query', 400);
        }

        $response = $this->getLocation($query);
        if ($response->status() != 200) {
            return response($response->getContent(), $response->status());
        }

        $location = json_decode($response->getContent());
        $restaurants = $this->getByLocation($location->entity_id, $location->entity_type);
        return response()->json(["restaurants" => $restaurants]);
    }

    private function getLocation($query) {
        $zomatoResponse = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/locations?query=' . $query, $this->defaultOption);
        $zomatoResponseBody = json_decode($zomatoResponse->getBody());
        $locations = $zomatoResponseBody->location_suggestions;
        if ($locations) {
            $location = $locations[0];
            $response = [
                'entity_id' => $location->entity_id,
                'entity_type' => $location->entity_type
            ];
            return response()->json($response);
        }
        return response('Location not found', 400);
    }

    private function getByLocation($locationId, $locationType) {
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/search?entity_id=' . $locationId . '&entity_type=' . $locationType, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $restaurants = [];
        foreach ($responseBody->restaurants as $res) {
            if ($res->restaurant->user_rating->rating_text == "Not rated") {
                $aggregate_rating = $res->restaurant->user_rating->rating_text;
            } else {
                $aggregate_rating = $res->restaurant->user_rating->aggregate_rating;
            }

            $restaurant = [
                'id' => $res->restaurant->R->res_id,
                'name' => $res->restaurant->name,
                'url' => $res->restaurant->url,
                'address' => $res->restaurant->location->address,
                'latitude' => $res->restaurant->location->latitude,
                'longitude' => $res->restaurant->location->longitude,
                'aggregate_rating' => $aggregate_rating,
                'menu_url' => $res->restaurant->menu_url,
                'featured_image' => $res->restaurant->featured_image
            ];
            $restaurants[] = $restaurant;
        }
        return $restaurants;
    }
}

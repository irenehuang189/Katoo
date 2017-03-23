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
        $zomatoResponse = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/restaurant?res_id=' . $id, $this->defaultOption);
        $zomatoResponseBody = json_decode($zomatoResponse->getBody());
        if ($zomatoResponseBody->R->res_id > 0) {
            if ($zomatoResponseBody->user_rating->rating_text == "Not rated") {
                $aggregate_rating = $zomatoResponseBody->user_rating->rating_text;
            } else {
                $aggregate_rating = $zomatoResponseBody->user_rating->aggregate_rating;
            }

            $response = [
                'id' => $id,
                'name' => $zomatoResponseBody->name,
                'url' => $zomatoResponseBody->url,
                'address' => $zomatoResponseBody->location->address,
                'latitude' => $zomatoResponseBody->location->latitude,
                'longitude' => $zomatoResponseBody->location->longitude,
                'aggregate_rating' => $aggregate_rating,
                'menu_url' => $zomatoResponseBody->menu_url,
                'featured_image' => $zomatoResponseBody->featured_image
            ];
            return response()->json($response);
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
        $restaurantResponse = $this->get($id);
        if ($restaurantResponse->status() != 200) {
            return response($restaurantResponse->getContent(), $restaurantResponse->status());
        }

        $zomatoResponse = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/reviews?res_id=' . $id, $this->defaultOption);
        $zomatoResponseBody = json_decode($zomatoResponse->getBody());
        $reviews = [];
        foreach ($zomatoResponseBody->user_reviews as $zomatoReview) {
            $review = [
                'rating' => $zomatoReview->review->rating,
                'review_text' => $zomatoReview->review->review_text
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

        $zomatoResponse = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/geocode?lat=' . $lat . '&lon=' . $long, $this->defaultOption);
        $zomatoResponseBody = json_decode($zomatoResponse->getBody());
        $restaurants = [];
        foreach ($zomatoResponseBody->nearby_restaurants as $nearby_restaurant) {
            if ($nearby_restaurant->restaurant->user_rating->rating_text == "Not rated") {
                $aggregate_rating = $nearby_restaurant->restaurant->user_rating->rating_text;
            } else {
                $aggregate_rating = $nearby_restaurant->restaurant->user_rating->aggregate_rating;
            }

            $restaurant = [
                'id' => $nearby_restaurant->restaurant->R->res_id,
                'name' => $nearby_restaurant->restaurant->name,
                'url' => $nearby_restaurant->restaurant->url,
                'address' => $nearby_restaurant->restaurant->location->address,
                'latitude' => $nearby_restaurant->restaurant->location->latitude,
                'longitude' => $nearby_restaurant->restaurant->location->longitude,
                'aggregate_rating' => $aggregate_rating,
                'menu_url' => $nearby_restaurant->restaurant->menu_url,
                'featured_image' => $nearby_restaurant->restaurant->featured_image
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
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/locations?query=' . $query, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        $locations = $responseBody->location_suggestions;
        if ($locations) {
            return response()->json($locations[0]);
        }
        return response('Location not found', 400);
    }

    private function getByLocation($locationId, $locationType) {
        $response = $this->client->request('GET', 'https://developers.zomato.com/api/v2.1/search?entity_id=' . $locationId . '&entity_type=' . $locationType, $this->defaultOption);
        $responseBody = json_decode($response->getBody());
        return $responseBody->restaurants;
    }
}

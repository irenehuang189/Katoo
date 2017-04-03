<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function getTitleOfUpcomingMovies(int $skipNum, int $takeNum) {
        $result = DB::table('upcominginfo')->skip($skipNum)->take($takeNum)->select('id', 'name', 'poster', 'genre')->get();
        return $result;
    }

    public function getDetailsById(int $dbId, string $state) {
        $table = ($state == 'upcoming') ? 'upcominginfo' : 'nowplayinginfo';
        $result = DB::table($table)->where('id', $dbId)->get();
        return $result;
    }

    public function getAllMovies() {
        $upcomings = DB::table('upcominginfo')->pluck('name');
        $playings = DB::table('nowplayinginfo')->pluck('name');

        $movie = [];
        foreach ($upcomings as $upcoming) {
            array_push($movie, $upcoming);
        }
        foreach ($playings as $playing) {
            array_push($movie, $playing);
        }

        return response()->json([
            'movies' => $movie
        ]);
    }
}

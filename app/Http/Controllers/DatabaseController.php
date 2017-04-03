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
        $upcoming = DB::table('upcominginfo')->pluck('name');
        // return $upcoming;
        var_dump(array_keys($upcoming));
        $playing = DB::table('nowplayinginfo')->pluck('name');
        // var_dump($playing);
        // die;
        return $upcoming + $playing;
    }
}

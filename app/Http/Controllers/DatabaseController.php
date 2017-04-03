<?php

namespace App\Http\Controllers;

use App\NowPlayingCinema;
use App\NowPlayingInfo;
use App\UpcomingInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function getTitleOfUpcomingMovies(int $skipNum, int $takeNum) {
        $result = DB::table('upcoming_infos')->skip($skipNum)->take($takeNum)->select('id', 'name', 'poster', 'genre')->get();
        return $result;
    }

    public function getDetailsById(int $dbId, string $state) {
        $table = ($state == 'upcoming') ? 'upcoming_infos' : 'now_playing_infos';
        $result = DB::table($table)->where('id', $dbId)->get();
        return $result;
    }

    public function getAllMovies() {
        $upcomings = DB::table('upcoming_infos')->pluck('name');
        $playings = DB::table('now_playing_infos')->pluck('name');

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

    public function resetDatabase(Request $request) {
        NowPlayingCinema::truncate();
        NowPlayingInfo::truncate();
        UpcomingInfo::truncate();
        $requestBody = json_decode($request->getContent());
        
        $nowPlayingCinemas = $requestBody->nowplayingcinema;
        foreach ($nowPlayingCinemas as $nowPlayingCinemaData) {
            $nowPlayingCinema = new NowPlayingCinema;
            $nowPlayingCinema->movie = $nowPlayingCinemaData->movie;
            $nowPlayingCinema->name = $nowPlayingCinemaData->name;
            $nowPlayingCinema->showtime = $nowPlayingCinemaData->showtime;
            $nowPlayingCinema->price = $nowPlayingCinemaData->price;
            $nowPlayingCinema->auditype = $nowPlayingCinemaData->auditype;
            $nowPlayingCinema->save();
        }

        $nowPlayingInfos = $requestBody->nowplayinginfo;
        foreach ($nowPlayingInfos as $nowPlayingInfoData) {
            $nowPlayingInfo = new NowPlayingInfo;
            $nowPlayingInfo->name = $nowPlayingInfoData->name;
            $nowPlayingInfo->poster = $nowPlayingInfoData->poster;
            $nowPlayingInfo->genre = $nowPlayingInfoData->genre;
            $nowPlayingInfo->duration = $nowPlayingInfoData->duration;
            $nowPlayingInfo->trailer = $nowPlayingInfoData->trailer;
            $nowPlayingInfo->plot = $nowPlayingInfoData->plot;
            $nowPlayingInfo->save();
        }

        $upcomingInfos = $requestBody->upcominginfo;
        foreach ($upcomingInfos as $upcomingInfoData) {
            $upcomingInfo = new UpcomingInfo;
            $upcomingInfo->name = $upcomingInfoData->name;
            $upcomingInfo->poster = $upcomingInfoData->poster;
            $upcomingInfo->genre = $upcomingInfoData->genre;
            $upcomingInfo->duration = $upcomingInfoData->duration;
            $upcomingInfo->trailer = $upcomingInfoData->trailer;
            $upcomingInfo->plot = $upcomingInfoData->plot;
            $upcomingInfo->save();
        }

        return response('OK');
    }
}

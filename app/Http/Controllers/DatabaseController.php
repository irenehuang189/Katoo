<?php

namespace App\Http\Controllers;

use App\NowPlayingCinema;
use App\NowPlayingInfo;
use App\UpcomingInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function getTitleOfNowPlayingMovies(int $skipNum, int $takeNum) {
        $result = DB::table('now_playing_infos')->skip($skipNum)->take($takeNum)->select('id', 'name', 'poster', 'genre')->get();
        return $result;
    }

    public function getTitleOfUpcomingMovies(int $skipNum, int $takeNum) {
        $result = DB::table('upcoming_infos')->skip($skipNum)->take($takeNum)->select('id', 'name', 'poster', 'genre')->get();
        return $result;
    }

    public function getDetailsById($dbId, $state) {
        $table = ($state == 'upcoming') ? 'upcoming_infos' : 'now_playing_infos';
        $result = DB::table($table)->where('id', $dbId)->first();
        return $result;
    }

    public function getDetailsByName($movieName) {
        $result = DB::table('now_playing_infos')->where('name', 'like', '%'.$movieName.'%')->first();
        if($result) {
            $result = (object) array_merge((array)$result, ['state' => 'nowplaying']);
        } else {
            $result = DB::table('upcoming_infos')->where('name', 'like', '%'.$movieName.'%')->first();
            if($result) {
                $result = (object) array_merge((array)$result, ['state' => 'upcoming']);
            }
        }
        
        return $result;
    }

    public function getCinema($dbId) {
        $info = DB::table('now_playing_infos')->where('id', $dbId)->select('name')->first();
        if(!isset($info->name)) {
            return null;
        }

        $result = DB::table('now_playing_cinemas')->where('movie', $info->name)->pluck('name');
        return $this->categorizeCinemaToCity($result);
    }

    public function getSchedule($dbId, $city) {
        $info = DB::table('now_playing_infos')->where('id', $dbId)->select('name')->first();
        if(!$info->name){
            return null;
        }

        $cinemas = $this->getAllCinemasInCity($city);
        $schedules = [];
        foreach ($cinemas as $cinema) {
            $schedule = DB::table('now_playing_cinemas')
                ->where('movie', '=', $info->name)
                ->where('name', '=', $cinema)
                ->select('showtime', 'price', 'auditype')
                ->get();
            $schedules = array_merge($schedules, [$cinema => $schedule->toArray()]);
        }
        
        return $schedules;
    }

    public function categorizeCinemaToCity($cinemas) {
        $data = [
            'Jakarta'   => ['Grand Indonesia', 'Pacific Place', 'Mall Of Indonesia', 'Central Park', 'Slipi Jaya', 'Bellatera Lifestyle Center', 'Green Pramuka Mall'],
            'Bandung'   => ['Paris Van Java', 'Miko Mall', 'BEC Mall', '23 Paskal'],
            'Tangerang' => ['Teraskota', 'Grand Dadap City', 'Ecoplaza Cikupa'],
            'Bekasi'    => ['Bekasi Cyber Park', 'Grand Galaxi Park'],
            'Batam'     => ['Kepri Mall', 'Harbour Bay'],
            'Yogyakarta'=> ['Jwalk Mall', 'Hartono Mall']
        ];
        $other = [
            'Balikpapan'=> 'Plaza Balikpapan',
            'Cirebon'   => 'Grage City Mall',
            'Surabaya'  => 'Marvell City',
            'Karawang'  => 'Festive Walk',
            'Manado'    => 'Kawanua Mall',
            'Purwokerto'=> 'Rita Supermall',
            'Mojokerto' => 'Sunrise Mall',
            'Medan'     => 'Focal Point Mall',
            'Palembang' => 'Social Market Palembang'
        ];

        $cities = [];
        foreach ($cinemas as $cinema) {
            foreach ($data as $cityData => $cinemaData) {
                if(in_array($cinema, $cinemaData)) {
                    if(!in_array($cityData, $cities)) {
                        array_push($cities, $cityData);
                    }
                }
            }
        }
        return $cities;
    }

    public function getAllCinemasInCity($city) {
        $data = [
            'Jakarta'   => ['Grand Indonesia', 'Pacific Place', 'Mall Of Indonesia', 'Central Park', 'Slipi Jaya', 'Bellatera Lifestyle Center', 'Green Pramuka Mall'],
            'Bandung'   => ['Paris Van Java', 'Miko Mall', 'BEC Mall', '23 Paskal'],
            'Tangerang' => ['Teraskota', 'Grand Dadap City', 'Ecoplaza Cikupa'],
            'Bekasi'    => ['Bekasi Cyber Park', 'Grand Galaxi Park'],
            'Batam'     => ['Kepri Mall', 'Harbour Bay'],
            'Yogyakarta'=> ['Jwalk Mall', 'Hartono Mall']
        ];
        $other = [
            'Balikpapan'=> 'Plaza Balikpapan',
            'Cirebon'   => 'Grage City Mall',
            'Surabaya'  => 'Marvell City',
            'Karawang'  => 'Festive Walk',
            'Manado'    => 'Kawanua Mall',
            'Purwokerto'=> 'Rita Supermall',
            'Mojokerto' => 'Sunrise Mall',
            'Medan'     => 'Focal Point Mall',
            'Palembang' => 'Social Market Palembang'
        ];

        return $data[$city];
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

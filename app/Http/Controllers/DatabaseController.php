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

    public function getCinema(int $dbId) {
        $info = DB::table('nowplayinginfo')->where('id', $dbId)->select('name')->first();
        if(!$info->name) {
            return null;
        }

        $result = DB::table('nowplayingcinema')->where('movie', $info->name)->pluck('name');
        return $this->categorizeCinemaToCity($result);
    }

    public function getSchedule(int $dbId, string $city) {
        $info = DB::table('nowplayinginfo')->where('id', $dbId)->select('name')->first();
        if(!$info->name){
            return null;
        }

        $cinemas = $this->getAllCinemasInCity($city);
        $schedules = [];
        foreach ($cinemas as $cinema) {
            $schedule = DB::table('nowplayingcinema')
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

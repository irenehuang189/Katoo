<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function getTitleOfUpcomingMovies(int $skipNum, int $takeNum) {
        $result = DB::table('upcominginfo')->skip($skipNum)->take($takeNum)->get();
        return $result;
    }
}

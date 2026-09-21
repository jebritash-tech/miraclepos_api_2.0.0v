<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;

class UnitController extends Controller
{

    public function index()
    {

        return Unit::where('active',true)

            ->orderBy('id')

            ->get();

    }

}
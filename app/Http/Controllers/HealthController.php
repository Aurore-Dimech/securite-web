<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index(){
        return json_encode(['test' => 'Hello world']);
    }
}

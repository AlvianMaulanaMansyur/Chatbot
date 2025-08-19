<?php

namespace App\Http\Controllers\chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        return view('chatbot.index');
    }
}

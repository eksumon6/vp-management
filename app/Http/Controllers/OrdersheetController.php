<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrdersheetController extends Controller
{
    public function index()
    {
        // For now, only show the dummy data view
        return view('ordersheet.index');
    }
}

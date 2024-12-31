<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class test extends Controller
{
    public function store(){
        return response()->json(['message' => 'Project created successfully'], 201);
    }
}

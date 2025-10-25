<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    // Ambil semua data level
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Level::all()
        ]);
    }
}
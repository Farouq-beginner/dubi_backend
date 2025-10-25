<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // Ambil semua data subject
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Subject::all()
        ]);
    }
}
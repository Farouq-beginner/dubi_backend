<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function checkUpdate()
    {
        return response()->json([
            'latest_version' => '1.0.1', // versi terbaru
            'build_number' => 3,
            'download_url' => 'https://drive.google.com/uc?export=download&id=XXXX',
            'force_update' => true, // paksa update
            'changelog' => '🚀 Peningkatan performa dan fitur baru di versi ini.'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function checkUpdate(Request $request)
    {
        $currentBuild = $request->query('build_number', 1); // build number dari aplikasi
        $latestBuild = 3; // build number terbaru di server

        $forceUpdate = $currentBuild < $latestBuild; // wajib update kalau build lama

        return response()->json([
            'latest_version' => '1.0.2',
            'latest_build' => $latestBuild,
            'update_required' => $forceUpdate,
            'download_url' => 'https://drive.google.com/drive/folders/1--6Q3oPzp7kHEWJocgfOIZzxbsMukOxo?usp=sharing',
            'changelog' => '🚀 Peningkatan performa dan fitur baru di versi ini.'
        ]);
    }
}

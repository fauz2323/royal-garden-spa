<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlamatSpa;
use Illuminate\Http\Request;

class AdminAlamatController extends Controller
{
    function getAlamat()
    {
        $alamat = AlamatSpa::first();
        return response()->json([
            'success' => true,
            'message' => 'Alamat retrieved successfully',
            'data' => $alamat
        ]);
    }

    function update(Request $request)
    {
        $request->validate([
            'alamat' => 'required|string',
            'kota' => 'required|string',
            'nomor_telepon' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $alamat = AlamatSpa::first();
        if (!$alamat) {
            $alamat = AlamatSpa::create($request->all());
        } else {
            $alamat->update($request->all());
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat updated successfully',
            'data' => $alamat
        ]);
    }
}

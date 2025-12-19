<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionAdminController extends Controller
{
    function index()
    {
        $missions = Mission::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Missions retrieved successfully',
            'data' => $missions
        ]);
    }

    function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'points' => 'required|integer|min:0',
            'goal' => 'required|integer|min:0',
        ]);

        $mission = Mission::create([
            'title' => $request->title,
            'description' => $request->description,
            'points' => $request->points,
            'goal' => $request->goal,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mission created successfully',
            'data' => $mission
        ]);
    }

    function detail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:missions,id',
        ]);

        $mission = Mission::find($request->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Mission created successfully',
            'data' => $mission
        ]);
    }
}

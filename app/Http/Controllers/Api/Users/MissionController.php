<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\UserHistoryPoint;
use App\Models\UserMission;
use App\Models\UserPoint;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    function index()
    {
        $mission = UserMission::where('user_id', auth()->id())->with('mission')->get();

        return response()->json([
            'success' => true,
            'message' => 'User missions retrieved successfully',
            'data' => $mission
        ], 200);
    }

    function claim($id)
    {
        $mission = UserMission::find($id);

        if ($mission->progress == $mission->mission->goal && $mission->claimed == 'incomplete') {
            $userPoint = UserPoint::where('user_id', auth()->id())->first();
            $userPoint->points += $mission->mission->points;
            $userPoint->save();

            $history = new UserHistoryPoint();
            $history->user_id = auth()->id();
            $history->points = $mission->mission->points;
            $history->description = 'Claimed mission: ' . $mission->mission->title;
            $history->save();

            $mission->claimed = 'completed';
            $mission->save();

            return response()->json([
                'success' => true,
                'message' => 'Mission claimed successfully',
                'data' => null
            ], 200);
        }


        return response()->json([
            'success' => true,
            'message' => 'Mission claimed unsuccessfully',
            'data' => null
        ], 222);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFcmToken;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    function setTokenUser(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'username' => 'required|string|exists:users,username',
        ]);


        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $fcmCheck = UserFcmToken::where('user_id', $user->id)->first();
        if ($fcmCheck) {
            $fcmCheck->fcm_token = $request->fcm_token;
            $fcmCheck->save();

            return response()->json(['message' => 'FCM token updated successfully'], 200);
        }

        $fcmToken = new UserFcmToken();
        $fcmToken->user_id = $user->id;
        $fcmToken->fcm_token = $request->fcm_token;
        $fcmToken->save();

        return response()->json(['message' => 'FCM token saved successfully'], 200);
    }
}

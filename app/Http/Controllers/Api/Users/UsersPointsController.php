<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\UserHistoryPoint;
use App\Models\UserPoint;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersPointsController extends Controller
{
    function index()
    {
        $userPoints = UserPoint::where('user_id', Auth::user()->id)->first();

        return response()->json([
            'success' => true,
            'message' => 'User points retrieved successfully',
            'data' => $userPoints
        ], 200);
    }


    function getHistory()
    {
        $history = UserHistoryPoint::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User points history retrieved successfully',
            'data' => $history
        ], 200);
    }

    function leaderboards()
    {
        $data = UserPoint::with('user')
            ->orderBy('points', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data' => $data
        ], 200);
    }

    function getVoucherShop()
    {
        $vouchers = Voucher::where('expiry_date', '>=', date('Y-m-d'))->where('isDelete', 'no')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Vouchers retrieved successfully',
            'data' => $vouchers
        ], 200);
    }

    function reedemVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
        ]);

        $id = $request->voucher_id;
        $voucher = Voucher::find($id);
        $points = UserPoint::where('user_id', Auth::id())->first();

        if ($voucher->price >= $points->points) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points to redeem this voucher',
            ], 400);
        }

        // Deduct points
        $points->points -= $voucher->price;
        $points->save();

        $userVoucer = new UserVoucher();
        $userVoucer->user_id = Auth::id();
        $userVoucer->voucher_id = $voucher->id;
        $userVoucer->status = 'unused';
        $userVoucer->save();

        $userHistoryPoint = new UserHistoryPoint();
        $userHistoryPoint->user_id = Auth::id();
        $userHistoryPoint->points = -$voucher->price;
        $userHistoryPoint->description = 'Reedem voucher: ' . $voucher->name;
        $userHistoryPoint->save();

        return response()->json([
            'success' => true,
            'message' => 'Voucher redeemed successfully',
            'data' => $userVoucer
        ], 200);
    }

    function reward()
    {
        $reward = UserVoucher::with('voucher')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User rewards retrieved successfully',
            'data' => $reward
        ], 200);
    }

    function useVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:user_vouchers,id',
        ]);

        $id = $request->voucher_id;
        $userVoucher = UserVoucher::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$userVoucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found for this user',
            ], 404);
        }

        if ($userVoucher->status == 'used') {
            return response()->json([
                'success' => false,
                'message' => 'Voucher has already been used',
            ], 400);
        }

        $userVoucher->status = 'used';
        $userVoucher->save();

        return response()->json([
            'success' => true,
            'message' => 'Voucher used successfully',
            'data' => $userVoucher
        ], 200);
    }

    function voucherDetail(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:user_vouchers,id',
        ]);

        $id = $request->voucher_id;
        $userVoucher = UserVoucher::where('id', $id)
            ->where('user_id', Auth::id())->with('voucher')
            ->first();

        if (!$userVoucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found for this user',
            ], 404);
        }


        return response()->json([
            'success' => true,
            'message' => 'Voucher get successfully',
            'data' => $userVoucher
        ], 200);
    }
}

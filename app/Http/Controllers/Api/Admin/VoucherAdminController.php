<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherAdminController extends Controller
{
    function index()
    {
        $vouchers = Voucher::where('isDelete', 'no')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => $vouchers
        ]);
    }

    function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'discount_amount' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        $voucher = Voucher::create([
            'name' => $request->name,
            'price' => $request->price,
            'discount_amount' => $request->discount_amount,
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher created successfully',
            'data' => $voucher
        ]);
    }

    function detail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:vouchers,id',
        ]);

        $voucher = Voucher::find($request->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher retrieved successfully',
            'data' => $voucher
        ]);
    }

    function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'discount_amount' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        $voucher = Voucher::find($request->id);
        $voucher->name = $request->name;
        $voucher->price = $request->price;
        $voucher->discount_amount = $request->discount_amount;
        $voucher->expiry_date = $request->expiry_date;
        $voucher->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher updated successfully',
            'data' => $voucher
        ]);
    }

    function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:vouchers,id',
        ]);

        $voucher = Voucher::find($request->id);
        $voucher->isDelete = 'yes';
        $voucher->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher deleted successfully',
        ]);
    }
}

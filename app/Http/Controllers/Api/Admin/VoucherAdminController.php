<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherAdminController extends Controller
{
    function index()
    {
        $vouchers = Voucher::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => $vouchers
        ]);
    }
}

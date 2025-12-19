<?php

namespace App\Exports;

use App\Models\UserOrders;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class UsersOrdersExport implements FromView
{
    public function view(): View
    {
        return view('exports.orders', [
            'orders' => UserOrders::with('user', 'spa_service')->get()
        ]);
    }
}

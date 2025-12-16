<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            [
                'name' => 'Welcome Voucher',
                'price' => '100',
                'discount_amount' => 15000,
                'expiry_date' => '2027-12-31',
            ],
            [
                'name' => 'Spring Special',
                'price' => '200',
                'discount_amount' => 25000,
                'expiry_date' => '2027-06-30',
            ],
            [
                'name' => 'Summer Relaxation',
                'price' => '300',
                'discount_amount' => 20000,
                'expiry_date' => '2027-09-30',
            ],
            [
                'name' => 'Holiday Package',
                'price' => '400',
                'discount_amount' => 50000,
                'expiry_date' => '2027-12-25',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}

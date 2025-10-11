<?php

namespace Database\Seeders;

use App\Models\SpaService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpaServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Traditional Balinese Massage',
                'description' => 'Relaksasi tubuh dengan pijat tradisional Bali menggunakan minyak aromaterapi alami untuk menghilangkan stress dan ketegangan otot.',
                'price' => 150000,
                'duration' => 90,
                'is_active' => true
            ],
            [
                'name' => 'Hot Stone Therapy',
                'description' => 'Terapi batu panas yang membantu melancarkan sirkulasi darah dan meredakan nyeri otot dengan sensasi hangat yang menenangkan.',
                'price' => 200000,
                'duration' => 120,
                'is_active' => true
            ],
            [
                'name' => 'Aromatherapy Facial',
                'description' => 'Perawatan wajah dengan aromaterapi untuk membersihkan, melembabkan, dan meremajakan kulit wajah Anda.',
                'price' => 125000,
                'duration' => 75,
                'is_active' => true
            ],
            [
                'name' => 'Deep Tissue Massage',
                'description' => 'Pijat otot dalam untuk mengatasi ketegangan kronis dan nyeri pada area tertentu dengan tekanan yang lebih kuat.',
                'price' => 175000,
                'duration' => 90,
                'is_active' => true
            ],
            [
                'name' => 'Reflexology',
                'description' => 'Terapi refleksi kaki untuk merangsang titik-titik saraf yang berhubungan dengan organ tubuh dan meningkatkan kesehatan.',
                'price' => 100000,
                'duration' => 60,
                'is_active' => true
            ],
            [
                'name' => 'Body Scrub & Wrap',
                'description' => 'Perawatan pengelupasan kulit mati dan pembungkus tubuh untuk kulit yang lebih halus dan lembab.',
                'price' => 180000,
                'duration' => 105,
                'is_active' => true
            ],
            [
                'name' => 'Couple Massage Package',
                'description' => 'Paket pijat untuk pasangan dalam ruangan khusus dengan suasana romantis dan menenangkan.',
                'price' => 350000,
                'duration' => 120,
                'is_active' => true
            ],
            [
                'name' => 'Prenatal Massage',
                'description' => 'Pijat khusus untuk ibu hamil yang aman dan nyaman untuk mengurangi ketegangan dan stress selama kehamilan.',
                'price' => 160000,
                'duration' => 75,
                'is_active' => false
            ]
        ];

        foreach ($services as $service) {
            SpaService::create($service);
        }

        $this->command->info('Spa services seeded successfully!');
    }
}

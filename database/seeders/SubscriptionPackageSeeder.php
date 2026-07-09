<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'nama_layanan' => 'Personal Harian',
                'harga' => 10000,
                'durasi' => 1,
                'durasi_type' => 'harian',
                'status' => 'publish',
                'audience_type' => 'personal',
                'category' => 'personal',
                'max_concurrent_sessions' => 1,
                'max_templates' => 10,
                'urutan' => 1,
            ],
            [
                'nama_layanan' => 'Personal Mingguan',
                'harga' => 50000,
                'durasi' => 1,
                'durasi_type' => 'mingguan',
                'status' => 'publish',
                'audience_type' => 'personal',
                'category' => 'personal',
                'max_concurrent_sessions' => 1,
                'max_templates' => 10,
                'urutan' => 2,
            ],
            [
                'nama_layanan' => 'Personal Bulanan',
                'harga' => 150000,
                'durasi' => 1,
                'durasi_type' => 'bulanan',
                'status' => 'publish',
                'audience_type' => 'personal',
                'category' => 'personal',
                'max_concurrent_sessions' => 1,
                'max_templates' => 10,
                'urutan' => 3,
            ],
            [
                'nama_layanan' => 'Personal Tahunan',
                'harga' => 1500000,
                'durasi' => 1,
                'durasi_type' => 'tahunan',
                'status' => 'publish',
                'audience_type' => 'personal',
                'category' => 'personal',
                'max_concurrent_sessions' => 1,
                'max_templates' => 10,
                'urutan' => 4,
            ],
            [
                'nama_layanan' => 'Organisasi Harian',
                'harga' => 20000,
                'durasi' => 1,
                'durasi_type' => 'harian',
                'status' => 'publish',
                'audience_type' => 'organization',
                'category' => 'organisasi',
                'max_concurrent_sessions' => 5,
                'max_templates' => 50,
                'urutan' => 5,
            ],
            [
                'nama_layanan' => 'Organisasi Mingguan',
                'harga' => 100000,
                'durasi' => 1,
                'durasi_type' => 'mingguan',
                'status' => 'publish',
                'audience_type' => 'organization',
                'category' => 'organisasi',
                'max_concurrent_sessions' => 5,
                'max_templates' => 50,
                'urutan' => 6,
            ],
            [
                'nama_layanan' => 'Organisasi Bulanan',
                'harga' => 300000,
                'durasi' => 1,
                'durasi_type' => 'bulanan',
                'status' => 'publish',
                'audience_type' => 'organization',
                'category' => 'organisasi',
                'max_concurrent_sessions' => 5,
                'max_templates' => 50,
                'urutan' => 7,
            ],
            [
                'nama_layanan' => 'Organisasi Tahunan',
                'harga' => 3000000,
                'durasi' => 1,
                'durasi_type' => 'tahunan',
                'status' => 'publish',
                'audience_type' => 'organization',
                'category' => 'organisasi',
                'max_concurrent_sessions' => 5,
                'max_templates' => 50,
                'urutan' => 8,
            ],
        ];

        foreach ($packages as $package) {
            Layanan::firstOrCreate(
                ['nama_layanan' => $package['nama_layanan']],
                $package
            );
        }
    }
}

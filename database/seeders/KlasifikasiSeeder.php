<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Klasifikasi;

class KlasifikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['klasifikasi_id' => 1,  'nama_klasifikasi' => 'Geografi',                        'icon' => 'globe'],
            ['klasifikasi_id' => 2,  'nama_klasifikasi' => 'Iklim',                            'icon' => 'leaf'],
            ['klasifikasi_id' => 3,  'nama_klasifikasi' => 'Pemerintahan',                     'icon' => 'shield'],
            ['klasifikasi_id' => 4,  'nama_klasifikasi' => 'Kependudukan',                     'icon' => 'users'],
            ['klasifikasi_id' => 5,  'nama_klasifikasi' => 'Ketenagakerjaan',                  'icon' => 'briefcase'],
            ['klasifikasi_id' => 6,  'nama_klasifikasi' => 'Sosial',                           'icon' => 'users'],
            ['klasifikasi_id' => 7,  'nama_klasifikasi' => 'Hukum',                            'icon' => 'scale'],
            ['klasifikasi_id' => 8,  'nama_klasifikasi' => 'Kebudayaan',                       'icon' => 'book-open'],
            ['klasifikasi_id' => 9,  'nama_klasifikasi' => 'Pendidikan',                       'icon' => 'graduation-cap'],
            ['klasifikasi_id' => 10, 'nama_klasifikasi' => 'Kesehatan',                       'icon' => 'heart-pulse'],
            ['klasifikasi_id' => 11, 'nama_klasifikasi' => 'Kepemudaan dan Olahraga',          'icon' => 'users'],
            ['klasifikasi_id' => 12, 'nama_klasifikasi' => 'Ketertiban Umum',                 'icon' => 'key'],
            ['klasifikasi_id' => 13, 'nama_klasifikasi' => 'Pertanian',                        'icon' => 'leaf'],
            ['klasifikasi_id' => 14, 'nama_klasifikasi' => 'Kehutanan',                        'icon' => 'tree'],
            ['klasifikasi_id' => 15, 'nama_klasifikasi' => 'Perkebunan',                       'icon' => 'leaf'],
            ['klasifikasi_id' => 16, 'nama_klasifikasi' => 'Peternakan',                       'icon' => 'tree'],
            ['klasifikasi_id' => 17, 'nama_klasifikasi' => 'Kelautan dan Perikanan',           'icon' => 'ship'],
            ['klasifikasi_id' => 18, 'nama_klasifikasi' => 'Industri',                         'icon' => 'factory'],
            ['klasifikasi_id' => 19, 'nama_klasifikasi' => 'Energi',                           'icon' => 'zap'],
            ['klasifikasi_id' => 20, 'nama_klasifikasi' => 'Pertambangan',                     'icon' => 'tools'],
            ['klasifikasi_id' => 21, 'nama_klasifikasi' => 'Komunikasi dan Informatika',       'icon' => 'globe'],
            ['klasifikasi_id' => 22, 'nama_klasifikasi' => 'Koperasi dan UMKM',                'icon' => 'briefcase'],
            ['klasifikasi_id' => 23, 'nama_klasifikasi' => 'Pariwisata',                       'icon' => 'ship'],
            ['klasifikasi_id' => 24, 'nama_klasifikasi' => 'Perdagangan',                      'icon' => 'shopping-cart'],
            ['klasifikasi_id' => 25, 'nama_klasifikasi' => 'Infrastruktur',                    'icon' => 'building'],
            ['klasifikasi_id' => 26, 'nama_klasifikasi' => 'Perhubungan',                      'icon' => 'bus'],
            ['klasifikasi_id' => 27, 'nama_klasifikasi' => 'Lingkungan Hidup',                 'icon' => 'leaf'],
            ['klasifikasi_id' => 28, 'nama_klasifikasi' => 'Harga',                            'icon' => 'chart-bar'],
            ['klasifikasi_id' => 29, 'nama_klasifikasi' => 'Pengeluaran dan Konsumsi',         'icon' => 'shopping-cart'],
            ['klasifikasi_id' => 30, 'nama_klasifikasi' => 'Pendapatan Regional',              'icon' => 'chart-bar'],
            ['klasifikasi_id' => 99, 'nama_klasifikasi' => 'Lainnya',                         'icon' => 'tag'],
        ];

        foreach ($data as $item) {
            Klasifikasi::updateOrCreate(
                ['klasifikasi_id' => $item['klasifikasi_id']],
                [
                    'nama_klasifikasi' => $item['nama_klasifikasi'],
                    'icon' => $item['icon'] ?? null,
                ]
            );
        }
    }
}
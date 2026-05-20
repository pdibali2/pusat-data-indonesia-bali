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
            ['klasifikasi_id' => 1,  'nama_klasifikasi' => 'Geografi'],
            ['klasifikasi_id' => 2,  'nama_klasifikasi' => 'Iklim'],
            ['klasifikasi_id' => 3,  'nama_klasifikasi' => 'Pemerintahan'],
            ['klasifikasi_id' => 4,  'nama_klasifikasi' => 'Kependudukan'],
            ['klasifikasi_id' => 5,  'nama_klasifikasi' => 'Ketenagakerjaan'],
            ['klasifikasi_id' => 6,  'nama_klasifikasi' => 'Sosial'],
            ['klasifikasi_id' => 7,  'nama_klasifikasi' => 'Hukum'],
            ['klasifikasi_id' => 8,  'nama_klasifikasi' => 'Kebudayaan'],
            ['klasifikasi_id' => 9,  'nama_klasifikasi' => 'Pendidikan'],
            ['klasifikasi_id' => 10, 'nama_klasifikasi' => 'Kesehatan'],
            ['klasifikasi_id' => 11, 'nama_klasifikasi' => 'Kepemudaan dan Olahraga'],
            ['klasifikasi_id' => 12, 'nama_klasifikasi' => 'Ketertiban Umum'],
            ['klasifikasi_id' => 13, 'nama_klasifikasi' => 'Pertanian'],
            ['klasifikasi_id' => 14, 'nama_klasifikasi' => 'Kehutanan'],
            ['klasifikasi_id' => 15, 'nama_klasifikasi' => 'Perkebunan'],
            ['klasifikasi_id' => 16, 'nama_klasifikasi' => 'Peternakan'],
            ['klasifikasi_id' => 17, 'nama_klasifikasi' => 'Kelautan dan Perikanan'],
            ['klasifikasi_id' => 18, 'nama_klasifikasi' => 'Industri'],
            ['klasifikasi_id' => 19, 'nama_klasifikasi' => 'Energi'],
            ['klasifikasi_id' => 20, 'nama_klasifikasi' => 'Pertambangan'],
            ['klasifikasi_id' => 21, 'nama_klasifikasi' => 'Komunikasi dan Informatika'],
            ['klasifikasi_id' => 22, 'nama_klasifikasi' => 'Koperasi dan UMKM'],
            ['klasifikasi_id' => 23, 'nama_klasifikasi' => 'Pariwisata'],
            ['klasifikasi_id' => 24, 'nama_klasifikasi' => 'Perdagangan'],
            ['klasifikasi_id' => 25, 'nama_klasifikasi' => 'Infrastruktur'],
            ['klasifikasi_id' => 26, 'nama_klasifikasi' => 'Perhubungan'],
            ['klasifikasi_id' => 27, 'nama_klasifikasi' => 'Lingkungan Hidup'],
            ['klasifikasi_id' => 28, 'nama_klasifikasi' => 'Harga'],
            ['klasifikasi_id' => 29, 'nama_klasifikasi' => 'Pengeluaran dan Konsumsi'],
            ['klasifikasi_id' => 30, 'nama_klasifikasi' => 'Pendapatan Regional'],
        ];

        foreach ($data as $item) {
            Klasifikasi::create([
                'klasifikasi_id' => $item['klasifikasi_id'],
                'nama_klasifikasi' => $item['nama_klasifikasi'],
            ]);
        }
    }
}
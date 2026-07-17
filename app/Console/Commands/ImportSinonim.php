<?php
// app/Console/Commands/ImportSinonim.php

namespace App\Console\Commands;

use App\Models\SinonimKata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSinonim extends Command
{
    protected $signature = 'sinonim:import
                            {path=database/data/sinonim-dict.json : Path ke file JSON dataset}
                            {--dry-run : Hanya hitung & preview, tidak insert ke DB}
                            {--fresh : Kosongkan data sumber=dataset sebelum import ulang}';

    protected $description = 'Import dataset sinonim (format tesaurus JSON) ke tabel sinonim_kata';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $this->warn('Pastikan lisensi dataset ini sudah dicek dan aman dipakai di produk berbayar sebelum lanjut.');
        if (!$this->option('dry-run') && !$this->confirm('Lanjutkan import ke database?', true)) {
            return self::SUCCESS;
        }

        $json = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('File JSON tidak valid: ' . json_last_error_msg());
            return self::FAILURE;
        }

        if (!$this->option('dry-run') && $this->option('fresh')) {
            SinonimKata::where('sumber', 'dataset')->delete();
            $this->info('Data lama (sumber=dataset) sudah dikosongkan.');
        }

        $rows = [];
        $totalGrup = 0;

        foreach ($json as $kataDasar => $entry) {
            $kataDasar = Str::lower(trim((string) $kataDasar));
            if ($kataDasar === '') continue;

            $sinonimList = $entry['sinonim'] ?? [];
            if (empty($sinonimList)) continue;

            $totalGrup++;

            // kata dasar itu sendiri juga masuk sebagai anggota grup
            $rows[] = ['kata' => $kataDasar, 'kata_dasar' => $kataDasar];

            foreach ($sinonimList as $s) {
                $s = Str::lower(trim((string) $s));
                if ($s === '' || $s === $kataDasar) continue;
                $rows[] = ['kata' => $s, 'kata_dasar' => $kataDasar];
            }
        }

        $this->info("Ditemukan {$totalGrup} grup sinonim, total " . count($rows) . " baris.");

        if ($this->option('dry-run')) {
            $this->table(['kata', 'kata_dasar'], array_slice($rows, 0, 20));
            $this->info('Dry-run selesai, tidak ada yang di-insert.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($rows));
        foreach (array_chunk($rows, 500) as $chunk) {
            $chunk = array_map(fn($r) => $r + [
                'sumber' => 'dataset',
                'created_at' => now(),
                'updated_at' => now(),
            ], $chunk);

            DB::table('sinonim_kata')->upsert(
                $chunk,
                ['kata', 'kata_dasar'],   // unique key
                ['updated_at']
            );
            $bar->advance(count($chunk));
        }
        $bar->finish();

        $this->newLine(2);
        $this->info('Import selesai.');
        return self::SUCCESS;
    }
}
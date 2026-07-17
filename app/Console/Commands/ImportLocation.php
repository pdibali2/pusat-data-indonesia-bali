<?php
// app/Console/Commands/ImportLocation.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLocation extends Command
{
    protected $signature = 'location:import
                            {path=database/data/location.json : Path ke file JSON (export phpMyAdmin)}
                            {--dry-run : Hanya hitung & preview, tidak insert ke DB}
                            {--fresh : Kosongkan tabel location sebelum import ulang}';

    protected $description = 'Import data wilayah Bali dari file JSON export phpMyAdmin ke tabel location';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $json = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('File JSON tidak valid: ' . json_last_error_msg());
            return self::FAILURE;
        }

        // Cari elemen bertipe "table" yang berisi key "data" (format export phpMyAdmin)
        $rows = null;
        foreach ($json as $item) {
            if (isset($item['type']) && $item['type'] === 'table' && isset($item['data'])) {
                $rows = $item['data'];
                break;
            }
        }

        // Fallback: kalau bukan format phpMyAdmin, anggap file-nya array data langsung
        if ($rows === null) {
            $rows = $json;
        }

        if (empty($rows)) {
            $this->error('Tidak ditemukan data location di dalam file JSON.');
            return self::FAILURE;
        }

        $this->info('Total data ditemukan: ' . count($rows));

        if ($this->option('dry-run')) {
            $this->table(
                array_keys($rows[0]),
                array_slice($rows, 0, 20)
            );
            $this->info('Dry-run selesai, tidak ada yang di-insert.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Lanjutkan import ke database?', true)) {
            return self::SUCCESS;
        }

        if ($this->option('fresh')) {
            DB::table('location')->truncate();
            $this->info('Tabel location sudah dikosongkan.');
        }

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach (array_chunk($rows, 500) as $chunk) {
            $upsertData = array_map(function ($row) {
                return [
                    'location_id'  => (string) ($row['location_id'] ?? ''),
                    'nama_wilayah' => $row['nama_wilayah'] ?? '',
                ];
            }, $chunk);

            DB::table('location')->upsert(
                $upsertData,
                ['location_id'],       // unique key
                ['nama_wilayah']       // kolom yang di-update kalau sudah ada
            );

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Import location selesai.');

        return self::SUCCESS;
    }
}
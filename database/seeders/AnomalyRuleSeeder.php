<?php

namespace Database\Seeders;

use App\Models\AnomalyRule;
use Illuminate\Database\Seeder;

class AnomalyRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [

            // ══════════════════════════════════════════════════
            // GLOBAL RULES — berlaku untuk semua metadata
            // ══════════════════════════════════════════════════

            [
                'name'                => 'Perubahan Persentase Global',
                'code'                => 'percentage_change',
                'rule_type'           => 'percentage_change',
                'threshold_low'       => 20,
                'threshold_medium'    => 100,
                'threshold_high'      => 500,
                'threshold_critical'  => 5000,
                'metadata_id'         => null,
                'frekuensi'           => null,
                'is_active'           => true,
                'description'         => 'Rule global untuk mendeteksi perubahan persentase ekstrem. '
                                       . 'Berlaku untuk semua metadata dan semua frekuensi jika tidak ada rule yang lebih spesifik.',
            ],

            [
                'name'                => 'Konflik Antar Sumber Data',
                'code'                => 'source_conflict',
                'rule_type'           => 'source_conflict',
                'threshold_low'       => 5,
                'threshold_medium'    => 15,
                'threshold_high'      => 30,
                'threshold_critical'  => 50,
                'metadata_id'         => null,
                'frekuensi'           => null,
                'is_active'           => true,
                'description'         => 'Mendeteksi konflik nilai antara beberapa produsen/sumber data '
                                       . 'untuk metadata, lokasi, dan periode yang sama. '
                                       . 'Threshold dalam persen selisih dari rata-rata.',
            ],

            [
                'name'                => 'Nilai Tidak Wajar (Absolut)',
                'code'                => 'absolute_change',
                'rule_type'           => 'absolute_change',
                'threshold_low'       => 100,
                'threshold_medium'    => 1000,
                'threshold_high'      => 10000,
                'threshold_critical'  => 100000,
                'metadata_id'         => null,
                'frekuensi'           => null,
                'is_active'           => true,
                'description'         => 'Mendeteksi perubahan nilai absolut yang tidak wajar. '
                                       . 'Digunakan untuk data yang tidak cocok dengan threshold persentase.',
            ],

            // ══════════════════════════════════════════════════
            // RULES PER FREKUENSI — global metadata, frekuensi spesifik
            // ══════════════════════════════════════════════════

            // Ganti bagian rules per frekuensi di seeder
            [
                'name'               => 'Perubahan Persentase — Tahunan',
                'code'               => 'percentage_change_tahunan',  // ← unik
                'rule_type'          => 'percentage_change',
                'threshold_low'      => 20,
                'threshold_medium'   => 100,
                'threshold_high'     => 500,
                'threshold_critical' => 5000,
                'metadata_id'        => null,
                'frekuensi'          => 'tahunan',
                'is_active'          => true,
                'description'        => 'Rule perubahan persentase untuk data tahunan.',
            ],
            [
                'name'               => 'Perubahan Persentase — Semester',
                'code'               => 'percentage_change_semester',
                'rule_type'          => 'percentage_change',
                'threshold_low'      => 15,
                'threshold_medium'   => 75,
                'threshold_high'     => 300,
                'threshold_critical' => 3000,
                'metadata_id'        => null,
                'frekuensi'          => 'semester',
                'is_active'          => true,
                'description'        => 'Rule perubahan persentase untuk data semesteran.',
            ],
            [
                'name'               => 'Perubahan Persentase — Kuartal',
                'code'               => 'percentage_change_quarter',
                'rule_type'          => 'percentage_change',
                'threshold_low'      => 10,
                'threshold_medium'   => 50,
                'threshold_high'     => 200,
                'threshold_critical' => 2000,
                'metadata_id'        => null,
                'frekuensi'          => 'quarter',
                'is_active'          => true,
                'description'        => 'Rule perubahan persentase untuk data kuartalan.',
            ],
            [
                'name'               => 'Perubahan Persentase — Bulanan',
                'code'               => 'percentage_change_bulanan',
                'rule_type'          => 'percentage_change',
                'threshold_low'      => 10,
                'threshold_medium'   => 40,
                'threshold_high'     => 150,
                'threshold_critical' => 1000,
                'metadata_id'        => null,
                'frekuensi'          => 'bulanan',
                'is_active'          => true,
                'description'        => 'Rule perubahan persentase untuk data bulanan.',
            ],

        ];

        $this->command->info('  Menyimpan Anomaly Rules...');

        $inserted = 0;
        $skipped  = 0;

        foreach ($rules as $rule) {
            // Cek duplikat: kombinasi code + metadata_id + frekuensi harus unik
            $exists = AnomalyRule::where('code', $rule['code'])
                ->exists();

            if ($exists) {
                $skipped++;
                $this->command->line("  <fg=yellow>SKIP</> {$rule['name']} (sudah ada)");
                continue;
            }

            AnomalyRule::create($rule);
            $inserted++;
            $this->command->line("  <fg=green>OK</>   {$rule['name']}");
        }

        $this->command->newLine();
        $this->command->info("  Selesai: {$inserted} rules ditambahkan, {$skipped} dilewati.");
    }
}
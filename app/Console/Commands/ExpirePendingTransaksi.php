<?php

// app/Console/Commands/ExpirePendingTransaksi.php
namespace App\Console\Commands;

use App\Models\Transaksi;
use Illuminate\Console\Command;

class ExpirePendingTransaksi extends Command
{
    protected $signature = 'transaksi:expire-pending';
    protected $description = 'Tandai transaksi pending yang sudah lebih dari 24 jam sebagai expired';

    public function handle()
    {
        $count = Transaksi::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(24))
            ->update(['status' => 'cancelled']);

        $this->info("{$count} transaksi pending ditandai cancelled.");
    }
}
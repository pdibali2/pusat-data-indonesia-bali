<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->integer('transaksi_id')->autoIncrement();

            $table->integer('user_id');
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('user')
                  ->onDelete('restrict');

            $table->integer('layanan_id');
            $table->foreign('layanan_id')
                  ->references('layanan_id')
                  ->on('layanan')
                  ->onDelete('restrict');

            // Snapshot harga & nama saat transaksi terjadi
            $table->string('nama_layanan', 200);
            $table->decimal('harga', 15, 2);
            $table->unsignedInteger('durasi');
            $table->enum('durasi_type', ['harian', 'mingguan', 'bulanan', 'tahunan', 'selamanya']);

            // Midtrans
            $table->string('order_id', 100)->unique();   // format: TRX-{user_id}-{timestamp}
            $table->string('snap_token', 500)->nullable(); // token dari Midtrans Snap
            $table->string('payment_type', 50)->nullable(); // gopay, bca_va, credit_card, dll
            $table->string('midtrans_transaction_id', 100)->nullable();

            // Status transaksi
            // pending   = menunggu pembayaran
            // success   = pembayaran berhasil (settlement/capture)
            // failed    = gagal / expired / deny
            // cancelled = dibatalkan user
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');

            // Tanggal aktif langganan (diisi saat status = success)
            $table->dateTime('aktif_mulai')->nullable();
            $table->dateTime('aktif_sampai')->nullable();  // null = selamanya

            // Raw notification dari Midtrans (untuk audit/debug)
            $table->json('midtrans_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
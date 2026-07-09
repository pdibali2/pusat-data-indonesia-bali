<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSingleActiveSession;
use App\Models\Group;
use App\Models\Layanan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnsureSingleActiveSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_invalidates_previous_personal_sessions_when_a_new_login_occurs(): void
    {
        Group::insert([
            'group_id' => 3,
            'title' => 'Customer',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('secret123'),
            'activation' => 'activated',
            'group_id' => 3,
            'registerdate' => now(),
            'lastvisitdate' => now(),
        ]);

        $layanan = Layanan::create([
            'nama_layanan' => 'Personal Premium',
            'harga' => 100000,
            'durasi' => 1,
            'durasi_type' => 'bulanan',
            'status' => 'publish',
            'audience_type' => 'personal',
            'max_seats' => 1,
            'max_concurrent_sessions' => 1,
        ]);

        Transaksi::create([
            'user_id' => $user->user_id,
            'layanan_id' => $layanan->layanan_id,
            'nama_layanan' => $layanan->nama_layanan,
            'harga' => $layanan->harga,
            'durasi' => $layanan->durasi,
            'durasi_type' => $layanan->durasi_type,
            'order_id' => 'TRX-1-1',
            'status' => 'success',
            'aktif_mulai' => now(),
            'aktif_sampai' => now()->addMonth(),
        ]);

        $oldSession = UserSession::create([
            'user_id' => $user->user_id,
            'session_id' => 'old-session-id',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'old-browser',
            'login_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($user);
        $request = Request::create('/data', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('auth.password_confirmed_at', now()->timestamp);
        $request->session()->start();

        $middleware = new EnsureSingleActiveSession();
        $middleware->handle($request, function () {
            return response('ok');
        });

        $this->assertDatabaseMissing('user_sessions', ['session_id' => 'old-session-id']);
        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->user_id, 'session_id' => $request->session()->getId()]);
    }
}

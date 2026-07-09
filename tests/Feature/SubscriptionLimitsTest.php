<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Tampilan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\UserSession;
use App\Services\SessionLimitService;
use App\Services\SubscriptionLimitsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SubscriptionLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_enforces_concurrent_session_limit_for_personal_packages(): void
    {
        $user = User::factory()->create();

        $package = Layanan::create([
            'nama_layanan' => 'Paket Personal Uji',
            'harga' => 10000,
            'durasi' => 1,
            'durasi_type' => 'bulanan',
            'status' => 'publish',
            'category' => 'personal',
            'max_concurrent_sessions' => 1,
            'max_templates' => 10,
            'audience_type' => 'personal',
        ]);

        Transaksi::create([
            'user_id' => $user->user_id,
            'layanan_id' => $package->layanan_id,
            'nama_layanan' => $package->nama_layanan,
            'harga' => $package->harga,
            'durasi' => $package->durasi,
            'durasi_type' => $package->durasi_type,
            'order_id' => 'ORDER-1',
            'status' => 'success',
            'aktif_mulai' => now()->subDay(),
            'aktif_sampai' => now()->addMonth(),
        ]);

        UserSession::create([
            'user_id' => $user->user_id,
            'session_id' => 'session-a',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent-a',
            'login_at' => now()->subMinute(),
        ]);

        UserSession::create([
            'user_id' => $user->user_id,
            'session_id' => 'session-b',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent-b',
            'login_at' => now(),
        ]);

        $service = new SubscriptionLimitsService();
        $result = $service->enforceConcurrentSessionLimit($user, 'session-c');

        $this->assertSame(1, UserSession::where('user_id', $user->user_id)->count());
        $this->assertTrue($result['evicted_count'] >= 1);
        $this->assertFalse(UserSession::where('session_id', 'session-a')->exists());
        $this->assertTrue(UserSession::where('session_id', 'session-c')->exists());
    }

    public function test_it_marks_the_oldest_session_inactive_when_limit_is_exceeded(): void
    {
        $user = User::factory()->create();

        $package = Layanan::create([
            'nama_layanan' => 'Paket Personal Uji 2',
            'harga' => 10000,
            'durasi' => 1,
            'durasi_type' => 'bulanan',
            'status' => 'publish',
            'category' => 'personal',
            'max_concurrent_sessions' => 1,
            'max_templates' => 10,
            'audience_type' => 'personal',
        ]);

        Transaksi::create([
            'user_id' => $user->user_id,
            'layanan_id' => $package->layanan_id,
            'nama_layanan' => $package->nama_layanan,
            'harga' => $package->harga,
            'durasi' => $package->durasi,
            'durasi_type' => $package->durasi_type,
            'order_id' => 'ORDER-3',
            'status' => 'success',
            'aktif_mulai' => now()->subDay(),
            'aktif_sampai' => now()->addMonth(),
        ]);

        UserSession::create([
            'user_id' => $user->user_id,
            'session_id' => 'session-old',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test-agent-old',
            'login_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $request = new Request();
        $request->setLaravelSession(app('session.store'));
        $request->server->set('HTTP_USER_AGENT', 'test-agent-new');

        $service = app(SessionLimitService::class);
        $service->enforceSessionLimit($user, $request);

        $oldSession = UserSession::where('session_id', 'session-old')->first();
        $newSession = UserSession::where('session_id', $request->session()->getId())->first();

        $this->assertNotNull($oldSession);
        $this->assertFalse($oldSession->is_active);
        $this->assertTrue($newSession->is_active);
    }

    public function test_it_blocks_template_creation_when_limit_reached(): void
    {
        $user = User::factory()->create();

        $package = Layanan::create([
            'nama_layanan' => 'Paket Personal Template',
            'harga' => 10000,
            'durasi' => 1,
            'durasi_type' => 'bulanan',
            'status' => 'publish',
            'category' => 'personal',
            'max_concurrent_sessions' => 1,
            'max_templates' => 10,
            'audience_type' => 'personal',
        ]);

        Transaksi::create([
            'user_id' => $user->user_id,
            'layanan_id' => $package->layanan_id,
            'nama_layanan' => $package->nama_layanan,
            'harga' => $package->harga,
            'durasi' => $package->durasi,
            'durasi_type' => $package->durasi_type,
            'order_id' => 'ORDER-2',
            'status' => 'success',
            'aktif_mulai' => now()->subDay(),
            'aktif_sampai' => now()->addMonth(),
        ]);

        for ($i = 0; $i < 10; $i++) {
            Tampilan::create([
                'nama_tampilan' => 'Template '.$i,
                'user_id' => $user->user_id,
                'filter_params' => null,
            ]);
        }

        $service = new SubscriptionLimitsService();

        $this->assertFalse($service->canCreateTemplate($user));
        $this->assertSame(10, $service->getTemplateCountForUser($user));
    }
}

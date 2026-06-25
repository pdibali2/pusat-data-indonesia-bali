<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_reset_request_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
    }

    public function test_user_can_request_password_reset_link()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'test@example.com']);
    }

    public function test_reset_password_form_can_be_rendered()
    {
        $response = $this->get('/reset-password/test-token?email=test@example.com');

        $response->assertOk();
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'email'                 => 'test@example.com',
            'token'                 => $token,
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHas('reset_success', true);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'test@example.com']);
    }

    public function test_user_cannot_reset_password_with_invalid_token()
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('old-password'),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => Hash::make(Str::random(64)),
            'created_at' => now(),
        ]);

        $response = $this->post('/reset-password', [
            'email'                 => 'test@example.com',
            'token'                 => 'invalid-token',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}

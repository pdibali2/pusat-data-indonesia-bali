<?php

namespace App\Http\Controllers;

use App\Services\OrganizationInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function accept(Request $request, string $token, OrganizationInvitationService $invitationService)
    {
        $invitation = $invitationService->findPendingInvitationByToken($token);

        if (! $invitation) {
            return redirect()->route('login')
                ->withErrors(['invitation' => 'Tautan undangan tidak valid atau sudah kadaluwarsa.']);
        }

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->email !== $invitation->email) {
                return redirect('/data')
                    ->withErrors(['invitation' => 'Silakan logout dan login menggunakan akun yang diundang.']);
            }

            $membership = $invitationService->acceptInvitation($invitation, $user);

            if (! $membership) {
                return redirect('/data')
                    ->withErrors(['invitation' => 'Tidak dapat menerima undangan organisasi saat ini. Silakan hubungi pemilik organisasi.']);
            }

            return redirect('/data')
                ->with('success', 'Undangan organisasi berhasil diterima. Selamat bergabung!');
        }

        if ($invitation->user_id) {
            return redirect()->route('login', [
                'invitation_token' => $token,
                'email' => $invitation->email,
            ]);
        }

        return redirect()->route('register', [
            'invitation_token' => $token,
            'email' => $invitation->email,
        ]);
    }
}

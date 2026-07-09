<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\InviteOrganizationMemberRequest;
use App\Models\Layanan;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function create(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $selectedPlan = null;
        if ($request->filled('layanan_id')) {
            $selectedPlan = Layanan::where('layanan_id', $request->layanan_id)
                ->where('audience_type', 'organization')
                ->first();
        }

        return view('pages.organizations.create', compact('user', 'selectedPlan'));
    }

    public function store(StoreOrganizationRequest $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $organization = Organization::create([
            'name' => $request->name,
            'owner_id' => $user->user_id,
        ]);

        OrganizationMember::create([
            'organization_id' => $organization->organization_id,
            'user_id' => $user->user_id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        if ($request->filled('layanan_id')) {
            $plan = Layanan::where('layanan_id', $request->layanan_id)
                ->where('audience_type', 'organization')
                ->first();

            if ($plan) {
                $plan->update(['organization_id' => $organization->organization_id]);
            }
        }

        return redirect()->route('organizations.manage-team', $organization)
            ->with('success', 'Organisasi berhasil dibuat.');
    }

    public function team()
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $organization = Organization::where('owner_id', $user->user_id)
            ->with(['members.user', 'subscription'])
            ->first();

        if (! $organization) {
            return redirect()->route('organizations.create')
                ->with('error', 'Anda belum memiliki organisasi. Buat organisasi baru terlebih dahulu.');
        }

        return view('pages.organizations.manage-team', compact('organization'));
    }

    public function manageTeam(Organization $organization)
    {
        $user = Auth::user();

        if (! $user || $organization->owner_id !== $user->user_id) {
            abort(403);
        }

        $organization->load(['members.user']);

        return view('pages.organizations.manage-team', compact('organization'));
    }

    public function inviteMember(InviteOrganizationMemberRequest $request, Organization $organization)
    {
        $user = Auth::user();

        if (! $user || $organization->owner_id !== $user->user_id) {
            abort(403);
        }

        $activeMemberCount = $organization->activeMembers()->count();
        $maxSeats = (int) ($organization->subscription?->max_seats ?? 1);

        if ($activeMemberCount >= $maxSeats) {
            return back()->withErrors([
                'email' => 'Kapasitas anggota sudah penuh untuk paket organisasi ini. Silakan upgrade paket atau hapus anggota yang sudah tidak aktif.',
            ]);
        }

        $memberUser = User::where('email', $request->email)->first();

        $existingInvitation = OrganizationInvitation::pending()
            ->where('organization_id', $organization->organization_id)
            ->where('email', $request->email)
            ->first();

        if ($existingInvitation) {
            return back()->withErrors([
                'email' => 'Undangan untuk email tersebut sudah dikirim. Silakan minta anggota untuk mengecek inbox mereka.',
            ]);
        }

        if ($memberUser) {
            $existingMember = OrganizationMember::where('organization_id', $organization->organization_id)
                ->where('user_id', $memberUser->user_id)
                ->first();

            if ($existingMember) {
                return back()->withErrors([
                    'email' => 'Email tersebut sudah menjadi anggota organisasi ini.',
                ]);
            }
        }

        $token = Str::random(64);

        OrganizationInvitation::create([
            'organization_id' => $organization->organization_id,
            'email' => $request->email,
            'token' => $token,
            'user_id' => $memberUser?->user_id,
            'status' => 'invited',
            'expires_at' => now()->addDays(7),
        ]);

        if ($memberUser) {
            OrganizationMember::create([
                'organization_id' => $organization->organization_id,
                'user_id' => $memberUser->user_id,
                'role' => 'member',
                'status' => 'invited',
                'joined_at' => null,
            ]);
        }

        Mail::to($request->email)->send(new \App\Mail\OrganizationInvitationMail($organization, $request->email, $token));

        return back()->with('success', 'Undangan anggota berhasil dikirim.');
    }

    public function removeMember(Organization $organization, OrganizationMember $member)
    {
        $user = Auth::user();

        if (! $user || $organization->owner_id !== $user->user_id) {
            abort(403);
        }

        if ($member->organization_id !== $organization->organization_id) {
            abort(403);
        }

        $member->update([
            'status' => 'removed',
            'joined_at' => $member->joined_at,
        ]);

        return back()->with('success', 'Anggota berhasil dihapus dari organisasi.');
    }
}

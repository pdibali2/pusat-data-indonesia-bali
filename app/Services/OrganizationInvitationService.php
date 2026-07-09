<?php

namespace App\Services;

use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;

class OrganizationInvitationService
{
    public function findPendingInvitationByToken(string $token): ?OrganizationInvitation
    {
        return OrganizationInvitation::pending()
            ->where('token', $token)
            ->first();
    }

    public function acceptInvitationForUser(string $token, User $user): ?OrganizationMember
    {
        $invitation = $this->findPendingInvitationByToken($token);

        if (! $invitation || $invitation->email !== $user->email) {
            return null;
        }

        return $this->acceptInvitation($invitation, $user);
    }

    public function acceptInvitation(OrganizationInvitation $invitation, User $user): ?OrganizationMember
    {
        $organization = $invitation->organization;

        if (! $organization) {
            return null;
        }

        $subscription = $organization->subscription;
        $maxSeats = (int) ($subscription?->max_seats ?? 1);
        $activeMemberCount = $organization->activeMembers()->count();

        $membership = OrganizationMember::where('organization_id', $organization->organization_id)
            ->where('user_id', $user->user_id)
            ->first();

        if ($membership) {
            $membership->update([
                'status' => 'active',
                'joined_at' => now(),
            ]);
        } else {
            if ($activeMemberCount >= $maxSeats) {
                return null;
            }

            $membership = OrganizationMember::create([
                'organization_id' => $organization->organization_id,
                'user_id' => $user->user_id,
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $invitation->update([
            'status' => 'accepted',
            'user_id' => $user->user_id,
        ]);

        return $membership;
    }
}

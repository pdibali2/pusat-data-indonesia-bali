<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->group_id, [1, 2])) {
            abort(403);
        }

        $query = Organization::with('owner')->withCount('members');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $organizations = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pages.admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization)
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->group_id, [1, 2])) {
            abort(403);
        }

        $organization->load(['owner', 'members.user']);

        return view('pages.admin.organizations.show', compact('organization'));
    }
}

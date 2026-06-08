<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::withCount('user')->where('status', 1);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $groups = $query->paginate(10)->withQueryString();

        return view('pages.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('pages.groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100|unique:group,title',
        ]);

        Group::create(['title' => $request->title]);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group berhasil ditambahkan.');
    }

    public function show(Group $group)
    {
        $group->loadCount('user');
        return view('pages.groups.show', compact('group'));
    }

    public function edit(Group $group)
    {
        return view('pages.groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'title' => 'required|string|max:100|unique:group,title,' . $group->group_id . ',group_id',
        ]);

        $group->update(['title' => $request->title]);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group berhasil diperbarui.');
    }

    public function toggleStatus(Group $group)
    {
        $group->update(['status' => $group->status === 1 ? 0 : 1]);

        $status = $group->status === 1 ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.groups.index')
            ->with('success', "Group {$group->title} berhasil {$status}.");
    }

    public function destroy(Group $group)
    {
        return redirect()->route('admin.groups.index')
            ->with('error', 'Group tidak dapat dihapus. Gunakan tombol nonaktifkan untuk menonaktifkan group.');
    }
}
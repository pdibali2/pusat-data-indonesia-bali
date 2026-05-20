<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('group');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $users  = $query->latest()->paginate(10)->withQueryString();
        $groups = Group::all();

        return view('pages.users.index', compact('users', 'groups'));
    }

    public function create()
    {
        $groups = Group::all();
        return view('pages.users.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'username' => 'required|string|max:50|unique:user,username',
            'email'    => 'required|email|max:50|unique:user,email',
            'password' => 'required|string|min:8|confirmed',
            'group_id' => 'required|exists:group,group_id',
        ]);

        User::create([
            'name'          => $request->name,
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'group_id'      => $request->group_id,
            'block'         => 0,
            'registerdate'  => now(),
            'lastvisitdate' => now(),
            'activation'    => '',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        $user->load('group');
        return view('pages.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $groups = Group::all();
        return view('pages.users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'username' => 'required|string|max:50|unique:user,username,' . $user->user_id . ',user_id',
            'email'    => 'required|email|max:50|unique:user,email,' . $user->user_id . ',user_id',
            'password' => 'nullable|string|min:8|confirmed',
            'group_id' => 'required|exists:group,group_id',
        ]);

        $data = [
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'group_id' => $request->group_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
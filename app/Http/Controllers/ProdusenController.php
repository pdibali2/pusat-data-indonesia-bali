<?php

namespace App\Http\Controllers;

use App\Models\ProdusenData;
use Illuminate\Http\Request;

class ProdusenController extends Controller
{
    public function index(Request $request)
    {
        $query = ProdusenData::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produsen', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nama_contact_person', 'like', "%{$search}%")
                  ->orWhere('kontak', 'like', "%{$search}%");
            });
        }

        $produsen = $query->latest()->paginate(10)->withQueryString();

        return view('pages.produsen.index', compact('produsen'));
    }

    public function create()
    {
        return view('pages.produsen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produsen'      => 'required|string|max:100',
            'email'              => 'nullable|email|max:100',
            'nama_contact_person'=> 'nullable|string|max:100',
            'kontak'             => 'nullable|string|max:100',
            'alamat'             => 'nullable|string|max:255',
        ]);

        ProdusenData::create($request->only([
            'nama_produsen', 'email', 'nama_contact_person', 'kontak', 'alamat'
        ]));

        return redirect()->route('admin.produsen.index')
            ->with('success', 'Produsen berhasil ditambahkan.');
    }

    public function show(ProdusenData $produsen)
    {
        return view('pages.produsen.show', compact('produsen'));
    }

    public function edit(ProdusenData $produsen)
    {
        return view('pages.produsen.edit', compact('produsen'));
    }

    public function update(Request $request, ProdusenData $produsen)
    {
        $request->validate([
            'nama_produsen'      => 'required|string|max:100',
            'email'              => 'nullable|email|max:100',
            'nama_contact_person'=> 'nullable|string|max:100',
            'kontak'             => 'nullable|string|max:100',
            'alamat'             => 'nullable|string|max:255',
        ]);

        $produsen->update($request->only([
            'nama_produsen', 'email', 'nama_contact_person', 'kontak', 'alamat'
        ]));

        return redirect()->route('admin.produsen.index')
            ->with('success', 'Produsen berhasil diperbarui.');
    }

    public function destroy(ProdusenData $produsen)
    {
        try {
            $produsen->delete();
            return redirect()->route('admin.produsen.index')
                ->with('success', 'Produsen berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.produsen.index')
                ->with('error', 'Produsen tidak dapat dihapus karena masih memiliki data rujukan.');
        }
    }
}
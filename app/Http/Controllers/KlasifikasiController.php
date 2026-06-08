<?php

namespace App\Http\Controllers;

use App\Models\Klasifikasi;
use Illuminate\Http\Request;

class KlasifikasiController extends Controller
{
    // ── Index ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Klasifikasi::where('status', 1);

        if ($request->filled('search')) {
            $query->where('nama_klasifikasi', 'like', '%' . $request->search . '%');
        }

        $klasifikasis = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.klasifikasi.index', compact('klasifikasis'));
    }

    // ── Create ─────────────────────────────────────────────────
    public function create()
    {
        return view('pages.klasifikasi.create');
    }

    // ── Store ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nama_klasifikasi' => 'required|string|max:255|unique:klasifikasi,nama_klasifikasi',
        ], [
            'nama_klasifikasi.required' => 'Nama klasifikasi wajib diisi.',
            'nama_klasifikasi.unique'   => 'Nama klasifikasi sudah terdaftar.',
            'nama_klasifikasi.max'      => 'Nama klasifikasi maksimal 255 karakter.',
        ]);

        Klasifikasi::create($request->only('nama_klasifikasi'));

        return redirect()
            ->route('admin.klasifikasi.index')
            ->with('success', 'Klasifikasi berhasil ditambahkan.');
    }

    // ── Show ───────────────────────────────────────────────────
    public function show(Klasifikasi $klasifikasi)
    {
        return view('pages.klasifikasi.show', compact('klasifikasi'));
    }

    // ── Edit ───────────────────────────────────────────────────
    public function edit(Klasifikasi $klasifikasi)
    {
        return view('pages.klasifikasi.edit', compact('klasifikasi'));
    }

    // ── Update ─────────────────────────────────────────────────
    public function update(Request $request, Klasifikasi $klasifikasi)
    {
        $request->validate([
            'nama_klasifikasi' => 'required|string|max:255|unique:klasifikasi,nama_klasifikasi,' 
                                  . $klasifikasi->klasifikasi_id . ',klasifikasi_id',
        ], [
            'nama_klasifikasi.required' => 'Nama klasifikasi wajib diisi.',
            'nama_klasifikasi.unique'   => 'Nama klasifikasi sudah terdaftar.',
            'nama_klasifikasi.max'      => 'Nama klasifikasi maksimal 255 karakter.',
        ]);

        $klasifikasi->update($request->only('nama_klasifikasi'));

        return redirect()
            ->route('admin.klasifikasi.index')
            ->with('success', 'Klasifikasi berhasil diperbarui.');
    }

    // ── Toggle Status ─────────────────────────────────────────
    public function toggleStatus(Klasifikasi $klasifikasi)
    {
        $klasifikasi->update(['status' => $klasifikasi->status === 1 ? 0 : 1]);

        $status = $klasifikasi->status === 1 ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.klasifikasi.index')
            ->with('success', "Klasifikasi {$klasifikasi->nama_klasifikasi} berhasil {$status}.");
    }

    // ── Destroy ────────────────────────────────────────────────
    public function destroy(Klasifikasi $klasifikasi)
    {
        return redirect()
            ->route('admin.klasifikasi.index')
            ->with('error', 'Klasifikasi tidak dapat dihapus. Gunakan tombol nonaktifkan untuk menonaktifkan klasifikasi.');
    }
}
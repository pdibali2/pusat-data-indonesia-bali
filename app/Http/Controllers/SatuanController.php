<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatuanController extends Controller
{
    public function index(Request $request)
    {
        $query = Satuan::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_satuan', 'like', "%{$search}%")
                  ->orWhere('simbol', 'like', "%{$search}%");
            });
        }

        $satuans = $query->orderBy('satuan_id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('pages.satuan.index', compact('satuans'));
    }

    public function create()
    {
        return view('pages.satuan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan'    => 'required|string|max:100|unique:satuan,nama_satuan',
            'simbol'         => 'nullable|string|max:50',
            'nilai_konversi' => 'required|numeric|gt:0',
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique'   => 'Nama satuan sudah terdaftar.',
            'nilai_konversi.required' => 'Nilai konversi wajib diisi.',
            'nilai_konversi.numeric'  => 'Nilai konversi harus berupa angka.',
            'nilai_konversi.gt'       => 'Nilai konversi harus lebih besar dari 0.',
        ]);

        Satuan::create($request->only(['nama_satuan', 'simbol', 'nilai_konversi']));

        return redirect()->route('admin.satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Satuan $satuan)
    {
        return view('pages.satuan.edit', compact('satuan')); 
    }

    public function update(Request $request, Satuan $satuan)
    {
        $request->validate([
            'nama_satuan'    => [
                'required',
                'string',
                'max:100',
                Rule::unique('satuan', 'nama_satuan')->ignore($satuan->satuan_id, 'satuan_id'),
            ],
            'simbol'         => 'nullable|string|max:50',
            'nilai_konversi' => 'required|numeric|gt:0',
        ], [
            'nama_satuan.required' => 'Nama satuan wajib diisi.',
            'nama_satuan.unique'   => 'Nama satuan sudah terdaftar.',
            'nilai_konversi.required' => 'Nilai konversi wajib diisi.',
            'nilai_konversi.numeric'  => 'Nilai konversi harus berupa angka.',
            'nilai_konversi.gt'       => 'Nilai konversi harus lebih besar dari 0.',
        ]);

        $satuan->update($request->only(['nama_satuan', 'simbol', 'nilai_konversi']));

        return redirect()->route('admin.satuan.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }
}

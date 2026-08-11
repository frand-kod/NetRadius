<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bandwidth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BandwidthController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Index', [
            'bandwidths' => Bandwidth::orderBy('id')->paginate(15),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_bw' => ['required', 'string'],
            'rate_down' => ['required', 'integer', 'min:0'],
            'rate_down_unit' => ['required', 'in:Kbps,Mbps'],
            'rate_up' => ['required', 'integer', 'min:0'],
            'rate_up_unit' => ['required', 'in:Kbps,Mbps'],
            'burst' => ['required', 'string', 'max:128'],
        ]);

        Bandwidth::create($data);

        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil dibuat.');
    }

    public function edit(Bandwidth $bandwidth): \Inertia\Response
    {
        return Inertia::render('Admin/Bandwidth/Edit', [
            'bandwidth' => $bandwidth,
        ]);
    }

    public function update(Request $request, Bandwidth $bandwidth): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name_bw' => ['required', 'string'],
            'rate_down' => ['required', 'integer', 'min:0'],
            'rate_down_unit' => ['required', 'in:Kbps,Mbps'],
            'rate_up' => ['required', 'integer', 'min:0'],
            'rate_up_unit' => ['required', 'in:Kbps,Mbps'],
            'burst' => ['required', 'string', 'max:128'],
        ]);

        $bandwidth->update($data);

        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil diperbarui.');
    }

    public function destroy(Bandwidth $bandwidth): \Illuminate\Http\RedirectResponse
    {
        $bandwidth->delete();

        return redirect()->route('admin.bandwidths.index')->with('success', 'Bandwidth berhasil dihapus.');
    }
}

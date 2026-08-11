<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RouterController extends Controller
{
    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Index', [
            'routers' => Router::orderBy('id')->paginate(15),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'ip_address' => ['required', 'string', 'max:128'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'coordinates' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Online,Offline'],
            'last_seen' => ['nullable', 'date'],
            'coverage' => ['required', 'string', 'max:8'],
            'enabled' => ['boolean'],
        ]);

        Router::create($data);

        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil dibuat.');
    }

    public function edit(Router $router): \Inertia\Response
    {
        return Inertia::render('Admin/Router/Edit', [
            'router' => $router,
        ]);
    }

    public function update(Request $request, Router $router): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'ip_address' => ['required', 'string', 'max:128'],
            'username' => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'coordinates' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Online,Offline'],
            'last_seen' => ['nullable', 'date'],
            'coverage' => ['required', 'string', 'max:8'],
            'enabled' => ['boolean'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $router->update($data);

        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil diperbarui.');
    }

    public function destroy(Router $router): \Illuminate\Http\RedirectResponse
    {
        $router->delete();

        return redirect()->route('admin.routers.index')->with('success', 'Router berhasil dihapus.');
    }
}

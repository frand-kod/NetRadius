<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    /**
     * Registri dokumen yang bisa dirender. Kunci = segmen URL, nilai = file .md + judul.
     *
     * Konten dipisah ke resources/markdown/ sehingga pembaruan dokumentasi cukup
     * mengedit file .md tanpa mengubah kode aplikasi.
     */
    private const DOCS = [
        'how-to-use' => ['file' => 'how-to-use.md', 'title' => 'Panduan Penggunaan'],
        'freeradius' => ['file' => 'freeradius.md', 'title' => 'Integrasi FreeRADIUS'],
        'gowa-wa' => ['file' => 'gowa-wa-gateway.md', 'title' => 'GOWA WhatsApp Gateway'],
    ];

    public function show(string $doc = 'how-to-use'): Response
    {
        if (! array_key_exists($doc, self::DOCS)) {
            abort(404);
        }

        $meta = self::DOCS[$doc];
        $path = resource_path('markdown/'.$meta['file']);
        $markdown = File::exists($path) ? File::get($path) : '# Dokumentasi Belum Tersedia';

        return Inertia::render('Admin/Help', [
            'docs' => collect(self::DOCS)->map(fn (array $d, string $key) => [
                'key' => $key,
                'title' => $d['title'],
                'active' => $key === $doc,
            ])->values(),
            'title' => $meta['title'],
            'content' => Str::markdown($markdown),
        ]);
    }
}

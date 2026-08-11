<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->input('tab', 'activity');

        if ($tab === 'message') {
            $query = MessageLog::query();

            if ($request->filled('search')) {
                $s = $request->input('search');
                $query->where(function ($q) use ($s) {
                    $q->where('recipient', 'like', "%{$s}%")
                        ->orWhere('message_content', 'like', "%{$s}%");
                });
            }
            if ($request->filled('type')) {
                $query->where('message_type', $request->input('type'));
            }

            $logs = $query->latest('id')->paginate(20)->withQueryString();
            $types = MessageLog::query()->distinct()->orderBy('message_type')->pluck('message_type');
        } else {
            $tab = 'activity';
            $query = ActivityLog::query();

            if ($request->filled('search')) {
                $s = $request->input('search');
                $query->where(function ($q) use ($s) {
                    $q->where('description', 'like', "%{$s}%")
                        ->orWhere('type', 'like', "%{$s}%");
                });
            }
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            $logs = $query->latest('id')->paginate(20)->withQueryString();
            $types = ActivityLog::query()->distinct()->orderBy('type')->pluck('type');
        }

        return Inertia::render('Admin/Logs/Index', [
            'tab' => $tab,
            'logs' => $logs,
            'types' => $types,
            'filters' => $request->only(['search', 'type']),
        ]);
    }
}

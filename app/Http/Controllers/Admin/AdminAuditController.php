<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminAuditController extends Controller
{
    public function index(Request $request): Response
    {
        $action = $request->string('action')->toString();

        $logs = AuditLog::query()
            ->with(['user:id,name,email', 'workspace:id,name'])
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->latest()
            ->limit(200)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'created_at' => $log->created_at?->toIso8601String(),
                'user' => $log->user?->only(['id', 'name', 'email']),
                'workspace' => $log->workspace?->only(['id', 'name']),
            ]);

        return $this->inertiaPage('Admin/Audit', 'Audit Log', [
            'logs' => $logs,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => [
                'action' => $action,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        Gate::authorize('viewAuditLogs');

        return view('admin.audit-logs', ['logs' => AuditLog::query()->with('user')->latest()->paginate(30)]);
    }
}

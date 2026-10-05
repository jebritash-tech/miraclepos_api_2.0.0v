<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /* ============================================================
       GET /api/audit-logs
       ============================================================ */
    public function index(Request $request)
    {
        $q = AuditLog::query()->with('user:id,name,role');

        // فلترة
        if ($action = $request->input('action')) {
            $q->where('action', $action);
        }
        if ($userId = $request->input('user_id')) {
            $q->where('user_id', $userId);
        }
        if ($severity = $request->input('severity')) {
            $q->where('severity', $severity);
        }
        if ($from = $request->input('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $q->whereDate('created_at', '<=', $to);
        }
        if ($search = $request->input('q')) {
            $q->where(function ($sub) use ($search) {
                $sub->where('description', 'LIKE', "%{$search}%")
                    ->orWhere('user_name', 'LIKE', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 30), 100);
        $logs = $q->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data'  => $logs->items(),
            'meta'  => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
            ],
        ]);
    }

    /* ============================================================
       GET /api/audit-logs/summary — ملخص للنشاط
       ============================================================ */
    public function summary(Request $request)
    {
        $from = $request->input('from', now()->subDays(7)->toDateString());
        $to   = $request->input('to',   now()->toDateString());

        return response()->json([
            'by_action' => AuditLog::whereBetween('created_at', [$from, "{$to} 23:59:59"])
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(20)
                ->get(),

            'by_severity' => AuditLog::whereBetween('created_at', [$from, "{$to} 23:59:59"])
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->get(),

            'top_users' => AuditLog::whereBetween('created_at', [$from, "{$to} 23:59:59"])
                ->whereNotNull('user_id')
                ->selectRaw('user_id, user_name, COUNT(*) as count')
                ->groupBy('user_id', 'user_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ]);
    }

    /* ============================================================
       GET /api/audit-logs/{id} — تفاصيل سجل واحد
       ============================================================ */
    public function show(int $id)
    {
        $log = AuditLog::with('user:id,name,role')->findOrFail($id);
        return response()->json($log);
    }

    /* ============================================================
       GET /api/audit-logs/filters — قيم الفلاتر المتاحة
       ============================================================ */
    public function filters()
    {
        return response()->json([
            'actions' => AuditLog::select('action')->distinct()->pluck('action'),
            'users'   => AuditLog::whereNotNull('user_id')
                ->selectRaw('user_id, user_name')
                ->groupBy('user_id', 'user_name')
                ->get(),
        ]);
    }
}
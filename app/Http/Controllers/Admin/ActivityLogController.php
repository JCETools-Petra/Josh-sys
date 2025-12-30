<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Daftar aksi yang tersedia untuk filtering
     */
    private $availableActions = [
        'create' => 'Buat Baru',
        'update' => 'Perbarui',
        'delete' => 'Hapus',
        'restore' => 'Pulihkan',
        'force_delete' => 'Hapus Permanen',
        'view' => 'Lihat',
        'export' => 'Ekspor',
        'import' => 'Impor',
        'login' => 'Login',
        'logout' => 'Logout',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'action' => 'nullable|string|in:' . implode(',', array_keys($this->availableActions)),
            'property_id' => 'nullable|exists:properties,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Get statistics for the filtered results
        $statsQuery = ActivityLog::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->input('action'));
            })
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', $request->input('property_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            });

        // Calculate statistics
        $stats = [
            'total' => $statsQuery->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', today())->count(),
            'this_week' => (clone $statsQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_action' => (clone $statsQuery)->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
        ];

        // Get paginated logs with same filters
        $logs = ActivityLog::with(['user', 'property'])
            ->latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->input('action'));
            })
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', $request->input('property_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })
            ->paginate(25)
            ->withQueryString();

        // Get filter options
        $properties = Property::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $actions = $this->availableActions;

        return view('admin.activity_log.index', compact('logs', 'stats', 'properties', 'users', 'actions'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'action' => 'nullable|string|in:' . implode(',', array_keys($this->availableActions)),
            'property_id' => 'nullable|exists:properties,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Get filtered logs without pagination
        $logs = ActivityLog::with(['user', 'property'])
            ->latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->input('action'));
            })
            ->when($request->filled('property_id'), function ($query) use ($request) {
                $query->where('property_id', $request->input('property_id'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })
            ->get();

        $filename = 'activity_log_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'Waktu',
                'Pengguna',
                'Properti',
                'Aksi',
                'Deskripsi',
                'IP Address',
                'User Agent'
            ]);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'Sistem',
                    $log->property->name ?? '-',
                    $this->availableActions[$log->action] ?? ucfirst($log->action),
                    $log->description,
                    $log->ip_address,
                    $log->user_agent,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
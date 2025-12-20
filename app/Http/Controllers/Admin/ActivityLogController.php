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
        'login' => 'Login',
        'logout' => 'Logout',
        'export' => 'Ekspor',
        'import' => 'Impor',
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
}
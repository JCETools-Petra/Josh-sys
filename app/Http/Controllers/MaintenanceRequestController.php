<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MaintenanceRequest;
use App\Models\HotelRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of maintenance requests.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $query = MaintenanceRequest::where('property_id', $property->id)
            ->with(['hotelRoom', 'reporter', 'assignee']);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $maintenanceRequests = $query->orderBy('priority', 'asc')
            ->orderBy('reported_at', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total' => MaintenanceRequest::where('property_id', $property->id)->count(),
            'pending' => MaintenanceRequest::where('property_id', $property->id)->pending()->count(),
            'in_progress' => MaintenanceRequest::where('property_id', $property->id)->inProgress()->count(),
            'urgent' => MaintenanceRequest::where('property_id', $property->id)->urgent()->count(),
        ];

        return view('maintenance.index', compact('maintenanceRequests', 'property', 'stats'));
    }

    /**
     * Show the form for creating a new maintenance request.
     */
    public function create()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get all rooms for the property
        $rooms = HotelRoom::where('property_id', $property->id)
            ->orderBy('room_number')
            ->get();

        // Get maintenance staff (users with role hk or admin)
        $staff = User::whereIn('role', ['hk', 'admin', 'pengguna_properti'])
            ->where(function($q) use ($property) {
                $q->where('property_id', $property->id)
                  ->orWhereNull('property_id');
            })
            ->get();

        return view('maintenance.create', compact('property', 'rooms', 'staff'));
    }

    /**
     * Store a newly created maintenance request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $validated = $request->validate([
            'hotel_room_id' => 'nullable|exists:hotel_rooms,id',
            'location' => 'required_without:hotel_room_id|nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:plumbing,electrical,hvac,furniture,electronics,cleaning,painting,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $maintenanceRequest = MaintenanceRequest::create([
                'property_id' => $property->id,
                'hotel_room_id' => $validated['hotel_room_id'],
                'location' => $validated['location'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'status' => 'pending',
                'reported_by' => $user->id,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'estimated_cost' => $validated['estimated_cost'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'reported_at' => now(),
            ]);

            // If assigned, mark as acknowledged
            if (!empty($validated['assigned_to'])) {
                $maintenanceRequest->update([
                    'status' => 'acknowledged',
                    'acknowledged_at' => now(),
                ]);
            }

            // Update room status if applicable
            if ($maintenanceRequest->hotel_room_id) {
                $room = HotelRoom::find($maintenanceRequest->hotel_room_id);
                if ($room && in_array($room->status, ['vacant_clean', 'vacant_dirty'])) {
                    $room->update(['status' => 'maintenance']);
                }
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'create',
                'description' => "{$user->name} membuat maintenance request #{$maintenanceRequest->request_number}: {$maintenanceRequest->title}",
                'loggable_id' => $maintenanceRequest->id,
                'loggable_type' => MaintenanceRequest::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('maintenance.index')
                ->with('success', "Maintenance request berhasil dibuat! Request Number: {$maintenanceRequest->request_number}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create maintenance request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat maintenance request. Silakan coba lagi.');
        }
    }

    /**
     * Display the specified maintenance request.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($maintenanceRequest->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke maintenance request ini.');
        }

        $maintenanceRequest->load(['hotelRoom', 'reporter', 'assignee']);

        // Get maintenance staff for reassignment
        $staff = User::whereIn('role', ['hk', 'admin', 'pengguna_properti'])
            ->where(function($q) use ($property) {
                $q->where('property_id', $property->id)
                  ->orWhereNull('property_id');
            })
            ->get();

        return view('maintenance.show', compact('maintenanceRequest', 'property', 'staff'));
    }

    /**
     * Update the specified maintenance request.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($maintenanceRequest->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke maintenance request ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,acknowledged,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'actual_cost' => 'nullable|numeric|min:0',
            'completion_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $maintenanceRequest->status;
            $updates = ['status' => $validated['status']];

            // Handle status transitions
            if ($validated['status'] === 'acknowledged' && !$maintenanceRequest->acknowledged_at) {
                $updates['acknowledged_at'] = now();
            }

            if ($validated['status'] === 'in_progress' && !$maintenanceRequest->started_at) {
                $updates['started_at'] = now();
            }

            if ($validated['status'] === 'completed') {
                $updates['completed_at'] = now();
                $updates['actual_cost'] = $validated['actual_cost'] ?? $maintenanceRequest->estimated_cost;
                $updates['completion_notes'] = $validated['completion_notes'];

                // Update room status back to available
                if ($maintenanceRequest->hotel_room_id) {
                    $room = HotelRoom::find($maintenanceRequest->hotel_room_id);
                    if ($room && $room->status === 'maintenance') {
                        $room->update(['status' => 'vacant_dirty']);
                    }
                }
            }

            if (!empty($validated['assigned_to'])) {
                $updates['assigned_to'] = $validated['assigned_to'];
            }

            $maintenanceRequest->update($updates);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => "{$user->name} mengubah status maintenance request #{$maintenanceRequest->request_number} dari {$oldStatus} ke {$validated['status']}",
                'loggable_id' => $maintenanceRequest->id,
                'loggable_type' => MaintenanceRequest::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Maintenance request berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update maintenance request', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengupdate maintenance request. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified maintenance request.
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($maintenanceRequest->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke maintenance request ini.');
        }

        try {
            $requestNumber = $maintenanceRequest->request_number;
            $maintenanceRequest->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'delete',
                'description' => "{$user->name} menghapus maintenance request #{$requestNumber}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('maintenance.index')
                ->with('success', 'Maintenance request berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Failed to delete maintenance request', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus maintenance request. Silakan coba lagi.');
        }
    }
}

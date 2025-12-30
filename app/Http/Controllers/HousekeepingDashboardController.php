<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\HkTask;
use App\Models\LostAndFound;
use App\Models\User;
use Illuminate\Http\Request;

class HousekeepingDashboardController extends Controller
{
    /**
     * Display housekeeping staff dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // ⚡ PERFORMANCE FIX: Get room statistics with single query instead of 5 separate queries
        $roomStats = $property->hotelRooms()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalRooms = $roomStats->sum();
        $dirtyRooms = $roomStats->get('dirty', 0);
        $cleanRooms = $roomStats->get('vacant_clean', 0);
        $occupiedRooms = $roomStats->get('occupied', 0);
        $maintenanceRooms = $roomStats->get('needs_maintenance', 0);

        // Get today's tasks for this user (if regular HK staff)
        $myTodayTasks = HkTask::where('property_id', $property->id)
            ->whereDate('task_date', today())
            ->where('assigned_to', $user->id)
            ->with(['hotelRoom'])
            ->orderBy('priority', 'desc')
            ->get();

        // Get all today's tasks (if supervisor/manager)
        $allTodayTasks = HkTask::where('property_id', $property->id)
            ->whereDate('task_date', today())
            ->with(['hotelRoom', 'assignedTo'])
            ->orderBy('priority', 'desc')
            ->get();

        // Task statistics
        $taskStats = [
            'my_total' => $myTodayTasks->count(),
            'my_pending' => $myTodayTasks->where('status', 'pending')->count(),
            'my_in_progress' => $myTodayTasks->where('status', 'in_progress')->count(),
            'my_completed' => $myTodayTasks->where('status', 'completed')->count(),
            'all_total' => $allTodayTasks->count(),
            'all_pending' => $allTodayTasks->where('status', 'pending')->count(),
            'all_in_progress' => $allTodayTasks->where('status', 'in_progress')->count(),
            'all_completed' => $allTodayTasks->where('status', 'completed')->count(),
        ];

        // Rooms needing attention (dirty rooms, prioritize unassigned)
        $roomsToClean = $property->hotelRooms()
            ->dirty()
            ->with(['roomType', 'assignedHousekeeper', 'assignedBy'])
            ->orderByRaw('CASE WHEN assigned_hk_user_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->limit(10)
            ->get();

        // Get all HK staff for this property
        $hkStaff = User::where('property_id', $property->id)
            ->where('role', 'hk')
            ->orderBy('name')
            ->get();

        // My assigned rooms
        $myAssignedRooms = $property->hotelRooms()
            ->where('assigned_hk_user_id', $user->id)
            ->where('status', 'vacant_dirty')
            ->with(['roomType'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        // Lost & Found items (recent)
        $recentLostItems = LostAndFound::where('property_id', $property->id)
            ->stored()
            ->with(['hotelRoom', 'foundBy'])
            ->latest('date_found')
            ->limit(5)
            ->get();

        // My performance this month
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $myMonthlyTasks = HkTask::where('property_id', $property->id)
            ->where('assigned_to', $user->id)
            ->whereBetween('task_date', [$monthStart, $monthEnd])
            ->get();

        $myPerformance = [
            'total_tasks' => $myMonthlyTasks->count(),
            'completed_tasks' => $myMonthlyTasks->where('status', 'completed')->count(),
            'completion_rate' => $myMonthlyTasks->count() > 0
                ? round(($myMonthlyTasks->where('status', 'completed')->count() / $myMonthlyTasks->count()) * 100, 1)
                : 0,
            'avg_duration' => $myMonthlyTasks->where('status', 'completed')->avg('duration_minutes'),
            'avg_quality_score' => $myMonthlyTasks->where('status', 'completed')->whereNotNull('quality_score')->avg('quality_score'),
        ];

        return view('housekeeping.dashboard', compact(
            'property',
            'totalRooms',
            'dirtyRooms',
            'cleanRooms',
            'occupiedRooms',
            'maintenanceRooms',
            'myTodayTasks',
            'allTodayTasks',
            'taskStats',
            'roomsToClean',
            'myAssignedRooms',
            'recentLostItems',
            'myPerformance',
            'hkStaff'
        ));
    }

    /**
     * Quick action: Mark room as clean.
     */
    public function quickMarkClean(Request $request, HotelRoom $room)
    {
        $user = auth()->user();

        // Check if room is assigned to this user
        if ($room->assigned_hk_user_id !== $user->id) {
            return redirect()->back()
                ->with('error', 'Kamar ini tidak di-assign ke Anda.');
        }

        if ($room->status !== 'vacant_dirty') {
            return redirect()->back()
                ->with('error', 'Kamar ini tidak dalam status kotor.');
        }

        $room->update([
            'status' => 'vacant_clean',
            'last_cleaned_at' => now(),
            'last_cleaned_by' => $user->id,
            'cleaning_notes' => $request->input('cleaning_notes'),
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $room->property_id,
            'action' => 'update',
            'description' => $user->name . " menandai kamar {$room->room_number} sebagai bersih" .
                ($request->input('cleaning_notes') ? " dengan catatan: " . $request->input('cleaning_notes') : ""),
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', "Kamar {$room->room_number} berhasil ditandai sebagai bersih");
    }

    /**
     * Assign room to housekeeping staff.
     */
    public function assignRoom(Request $request, HotelRoom $room)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'assignment_notes' => 'nullable|string|max:1000',
        ]);

        $assignedTo = User::findOrFail($request->assigned_to);

        // Verify the assigned user is HK staff for this property
        if ($assignedTo->role !== 'hk' || $assignedTo->property_id !== $property->id) {
            return redirect()->back()
                ->with('error', 'User yang dipilih bukan staff housekeeping untuk properti ini.');
        }

        $room->update([
            'assigned_hk_user_id' => $request->assigned_to,
            'assigned_by' => $user->id,
            'assigned_at' => now(),
            'assignment_notes' => $request->assignment_notes,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $room->property_id,
            'action' => 'update',
            'description' => $user->name . " assign kamar {$room->room_number} ke {$assignedTo->name}" .
                ($request->assignment_notes ? " dengan catatan: " . $request->assignment_notes : ""),
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', "Kamar {$room->room_number} berhasil di-assign ke {$assignedTo->name}");
    }
}

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

        // Get room statistics
        $totalRooms = $property->hotelRooms()->count();
        $dirtyRooms = $property->hotelRooms()->dirty()->count();
        $cleanRooms = $property->hotelRooms()->available()->count();
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $maintenanceRooms = $property->hotelRooms()->needsMaintenance()->count();

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

        // Rooms needing attention
        $roomsToClean = $property->hotelRooms()
            ->dirty()
            ->with(['roomType', 'assignedHousekeeper'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->limit(10)
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
            'myPerformance'
        ));
    }

    /**
     * Quick action: Mark room as clean.
     */
    public function quickMarkClean(HotelRoom $room)
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
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $room->property_id,
            'action' => 'update',
            'description' => $user->name . " menandai kamar {$room->room_number} sebagai bersih (quick action dari dashboard)",
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', "Kamar {$room->room_number} berhasil ditandai sebagai bersih");
    }
}

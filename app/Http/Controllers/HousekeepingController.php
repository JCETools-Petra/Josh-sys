<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousekeepingController extends Controller
{
    /**
     * Display housekeeping dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Get room statistics
        $dirtyRooms = $property->hotelRooms()->dirty()->count();
        $cleanRooms = $property->hotelRooms()->available()->count();
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $maintenanceRooms = $property->hotelRooms()->needsMaintenance()->count();

        // Get rooms needing attention
        $roomsToClean = $property->hotelRooms()
            ->dirty()
            ->with(['roomType', 'assignedHousekeeper'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        // Get housekeeping staff
        $housekeepers = User::where('role', 'hk')
            ->where('property_id', $property->id)
            ->get();

        return view('housekeeping.index', compact(
            'property',
            'dirtyRooms',
            'cleanRooms',
            'occupiedRooms',
            'maintenanceRooms',
            'roomsToClean',
            'housekeepers'
        ));
    }

    /**
     * Update room status.
     */
    public function updateRoomStatus(HotelRoom $room, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:vacant_clean,vacant_dirty,occupied,maintenance,out_of_order,blocked',
        ]);

        $oldStatus = $room->status;
        $room->update([
            'status' => $validated['status'],
            'last_cleaned_at' => $validated['status'] === 'vacant_clean' ? now() : $room->last_cleaned_at,
        ]);

        // Log activity
        $statusLabel = match($validated['status']) {
            'vacant_clean' => 'kosong bersih',
            'vacant_dirty' => 'kosong kotor',
            'occupied' => 'terisi',
            'maintenance' => 'maintenance',
            'out_of_order' => 'out of order',
            'blocked' => 'diblokir',
            default => $validated['status']
        };

        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $room->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " mengubah status kamar {$room->room_number} dari {$oldStatus} menjadi {$statusLabel}",
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status kamar berhasil diupdate',
        ]);
    }

    /**
     * Assign housekeeper to room.
     */
    public function assignHousekeeper(HotelRoom $room, Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        // ✅ SECURITY FIX: Validate room belongs to user's property
        if ($room->property_id !== $property->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kamar ini.',
            ], 403);
        }

        // ✅ SECURITY FIX: Validate housekeeper belongs to same property
        $validated = $request->validate([
            'assigned_hk_user_id' => [
                'required',
                'exists:users,id',
                Rule::exists('users', 'id')->where('property_id', $property->id),
            ],
        ]);

        $room->update([
            'assigned_hk_user_id' => $validated['assigned_hk_user_id'],
        ]);

        // Log activity
        $housekeeper = User::find($validated['assigned_hk_user_id']);
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'update',
            'description' => $user->name . " meng-assign housekeeping staff '{$housekeeper->name}' ke kamar {$room->room_number}",
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Housekeeping staff berhasil di-assign',
        ]);
    }

    /**
     * Mark room as clean.
     */
    public function markAsClean(HotelRoom $room)
    {
        $room->markAsClean();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $room->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " menandai kamar {$room->room_number} sebagai bersih",
            'loggable_id' => $room->id,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', "Kamar {$room->room_number} telah ditandai sebagai bersih");
    }

    /**
     * Mark multiple rooms as clean.
     */
    public function bulkMarkAsClean(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:hotel_rooms,id',
        ]);

        // ✅ SECURITY FIX: Validate all rooms belong to user's property
        $rooms = HotelRoom::where('property_id', $property->id)
            ->whereIn('id', $validated['room_ids'])
            ->get();

        // Validate that all requested rooms exist and belong to this property
        if ($rooms->count() !== count($validated['room_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa kamar tidak ditemukan atau tidak termasuk dalam properti Anda.',
            ], 403);
        }

        $roomNumbers = $rooms->pluck('room_number')->implode(', ');

        // ✅ SECURITY FIX: Only update rooms that belong to this property
        HotelRoom::where('property_id', $property->id)
            ->whereIn('id', $validated['room_ids'])
            ->update([
                'status' => 'vacant_clean',
                'last_cleaned_at' => now(),
            ]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'update',
            'description' => $user->name . " menandai " . count($validated['room_ids']) . " kamar sebagai bersih (bulk): {$roomNumbers}",
            'loggable_id' => null,
            'loggable_type' => HotelRoom::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($validated['room_ids']) . ' kamar berhasil ditandai sebagai bersih',
        ]);
    }

    /**
     * Show performance report.
     */
    public function performanceReport(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        // Date range
        $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date') ? $request->end_date : now()->toDateString();

        // Get all housekeeping staff
        $housekeepers = User::where('role', 'hk')
            ->where('property_id', $property->id)
            ->get();

        $performanceData = [];

        foreach ($housekeepers as $hk) {
            // Get tasks completed by this staff
            $tasks = \App\Models\HkTask::where('property_id', $property->id)
                ->where('assigned_to', $hk->id)
                ->whereBetween('task_date', [$startDate, $endDate])
                ->get();

            $completedTasks = $tasks->where('status', 'completed');
            $totalTasks = $tasks->count();
            $completionRate = $totalTasks > 0 ? ($completedTasks->count() / $totalTasks) * 100 : 0;

            // Average duration
            $avgDuration = $completedTasks->avg('duration_minutes');

            // Average quality score
            $inspectedTasks = $completedTasks->whereNotNull('quality_score');
            $avgQualityScore = $inspectedTasks->avg('quality_score');

            // Count by task type
            $dailyCleaningCount = $completedTasks->where('task_type', 'daily_cleaning')->count();
            $deepCleaningCount = $completedTasks->where('task_type', 'deep_cleaning')->count();

            // Rooms cleaned
            $roomsCleaned = HotelRoom::where('property_id', $property->id)
                ->where('last_cleaned_by', $hk->id)
                ->whereBetween('last_cleaned_at', [$startDate, $endDate])
                ->count();

            $performanceData[] = [
                'staff' => $hk,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks->count(),
                'completion_rate' => round($completionRate, 1),
                'avg_duration' => $avgDuration ? round($avgDuration) : 0,
                'avg_quality_score' => $avgQualityScore ? round($avgQualityScore, 1) : null,
                'daily_cleaning_count' => $dailyCleaningCount,
                'deep_cleaning_count' => $deepCleaningCount,
                'rooms_cleaned' => $roomsCleaned,
            ];
        }

        // Sort by completion rate
        usort($performanceData, function($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });

        // Overall statistics
        $overallStats = [
            'total_tasks' => \App\Models\HkTask::where('property_id', $property->id)
                ->whereBetween('task_date', [$startDate, $endDate])
                ->count(),
            'completed_tasks' => \App\Models\HkTask::where('property_id', $property->id)
                ->whereBetween('task_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'avg_duration' => round(\App\Models\HkTask::where('property_id', $property->id)
                ->whereBetween('task_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->avg('duration_minutes')),
            'avg_quality_score' => round(\App\Models\HkTask::where('property_id', $property->id)
                ->whereBetween('task_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->whereNotNull('quality_score')
                ->avg('quality_score'), 1),
        ];

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'export',
            'description' => $user->name . " melihat performance report housekeeping dari {$startDate} sampai {$endDate}",
            'loggable_id' => null,
            'loggable_type' => 'HousekeepingPerformance',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('housekeeping.performance-report', compact(
            'performanceData',
            'overallStats',
            'startDate',
            'endDate',
            'property'
        ));
    }
}

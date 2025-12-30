<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\HkTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HkTaskController extends Controller
{
    /**
     * Display housekeeping tasks list.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $query = HkTask::where('property_id', $property->id)
            ->with(['hotelRoom', 'assignedTo', 'inspectedBy']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('task_date', $request->date);
        } else {
            $query->whereDate('task_date', today());
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->orderBy('priority', 'desc')
            ->orderBy('hotel_room_id')
            ->paginate(20);

        // Get housekeepers for filter
        $housekeepers = User::where('role', 'hk')
            ->where('property_id', $property->id)
            ->get();

        // Statistics
        $stats = [
            'total' => HkTask::where('property_id', $property->id)->whereDate('task_date', today())->count(),
            'pending' => HkTask::where('property_id', $property->id)->whereDate('task_date', today())->pending()->count(),
            'in_progress' => HkTask::where('property_id', $property->id)->whereDate('task_date', today())->where('status', 'in_progress')->count(),
            'completed' => HkTask::where('property_id', $property->id)->whereDate('task_date', today())->completed()->count(),
        ];

        return view('housekeeping.tasks.index', compact('tasks', 'housekeepers', 'stats', 'property'));
    }

    /**
     * Show form to create task.
     */
    public function create()
    {
        $user = auth()->user();
        $property = $user->property;

        $rooms = $property->hotelRooms()
            ->with('roomType')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        $housekeepers = User::where('role', 'hk')
            ->where('property_id', $property->id)
            ->get();

        return view('housekeeping.tasks.create', compact('property', 'rooms', 'housekeepers'));
    }

    /**
     * Store new task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'assigned_to' => 'nullable|exists:users,id',
            'task_date' => 'required|date',
            'task_type' => 'required|in:daily_cleaning,deep_cleaning,turndown,inspection',
            'priority' => 'required|in:low,normal,high,urgent',
            'notes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $property = $user->property;

        // Get default checklist
        $checklist = HkTask::getDefaultChecklist($validated['task_type']);

        $task = HkTask::create([
            'property_id' => $property->id,
            'hotel_room_id' => $validated['hotel_room_id'],
            'assigned_to' => $validated['assigned_to'],
            'task_date' => $validated['task_date'],
            'task_type' => $validated['task_type'],
            'priority' => $validated['priority'],
            'status' => 'pending',
            'checklist' => $checklist,
            'completed_items' => [],
            'notes' => $validated['notes'],
        ]);

        $room = HotelRoom::find($validated['hotel_room_id']);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'create',
            'description' => $user->name . " membuat task {$validated['task_type']} untuk kamar {$room->room_number}" . ($validated['assigned_to'] ? ", assign ke: " . User::find($validated['assigned_to'])->name : ''),
            'loggable_id' => $task->id,
            'loggable_type' => HkTask::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('housekeeping.tasks.index')
            ->with('success', 'Task berhasil dibuat');
    }

    /**
     * Show task detail.
     */
    public function show(HkTask $task)
    {
        $task->load(['hotelRoom', 'assignedTo', 'inspectedBy', 'property']);

        return view('housekeeping.tasks.show', compact('task'));
    }

    /**
     * Start task.
     */
    public function start(HkTask $task)
    {
        if ($task->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Task sudah dimulai atau selesai');
        }

        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $task->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " memulai task {$task->task_type} untuk kamar {$task->hotelRoom->room_number}",
            'loggable_id' => $task->id,
            'loggable_type' => HkTask::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Task dimulai');
    }

    /**
     * Update checklist progress.
     */
    public function updateChecklist(Request $request, HkTask $task)
    {
        $validated = $request->validate([
            'completed_items' => 'required|array',
            'completed_items.*' => 'string',
        ]);

        $task->update([
            'completed_items' => $validated['completed_items'],
        ]);

        return response()->json([
            'success' => true,
            'progress' => count($validated['completed_items']) . '/' . count($task->checklist ?? []),
        ]);
    }

    /**
     * Complete task.
     */
    public function complete(HkTask $task)
    {
        if ($task->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Task sudah selesai');
        }

        $task->markAsCompleted();

        // Update room status if daily cleaning
        if ($task->task_type === 'daily_cleaning') {
            $task->hotelRoom->update([
                'status' => 'vacant_clean',
                'last_cleaned_at' => now(),
                'last_cleaned_by' => auth()->id(),
            ]);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $task->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " menyelesaikan task {$task->task_type} untuk kamar {$task->hotelRoom->room_number}, durasi: {$task->duration_minutes} menit",
            'loggable_id' => $task->id,
            'loggable_type' => HkTask::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Task selesai');
    }

    /**
     * Inspect completed task.
     */
    public function inspect(Request $request, HkTask $task)
    {
        $validated = $request->validate([
            'quality_score' => 'required|integer|min:1|max:5',
            'inspection_notes' => 'nullable|string',
        ]);

        $task->update([
            'inspected_by' => auth()->id(),
            'inspected_at' => now(),
            'quality_score' => $validated['quality_score'],
            'inspection_notes' => $validated['inspection_notes'],
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $task->property_id,
            'action' => 'update',
            'description' => auth()->user()->name . " menginspeksi task {$task->task_type} kamar {$task->hotelRoom->room_number}, skor: {$validated['quality_score']}/5",
            'loggable_id' => $task->id,
            'loggable_type' => HkTask::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()
            ->with('success', 'Inspeksi berhasil dicatat');
    }

    /**
     * Generate auto tasks for dirty rooms.
     */
    public function generateAutoTasks()
    {
        $user = auth()->user();
        $property = $user->property;

        $dirtyRooms = $property->hotelRooms()->dirty()->get();
        $tasksCreated = 0;

        foreach ($dirtyRooms as $room) {
            // Check if task already exists for today
            $existingTask = HkTask::where('hotel_room_id', $room->id)
                ->whereDate('task_date', today())
                ->first();

            if (!$existingTask) {
                $checklist = HkTask::getDefaultChecklist('daily_cleaning');

                HkTask::create([
                    'property_id' => $property->id,
                    'hotel_room_id' => $room->id,
                    'assigned_to' => $room->assigned_hk_user_id,
                    'task_date' => today(),
                    'task_type' => 'daily_cleaning',
                    'priority' => 'normal',
                    'status' => 'pending',
                    'checklist' => $checklist,
                    'completed_items' => [],
                ]);

                $tasksCreated++;
            }
        }

        // Log activity
        if ($tasksCreated > 0) {
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'create',
                'description' => $user->name . " generate {$tasksCreated} auto tasks untuk kamar kotor",
                'loggable_id' => null,
                'loggable_type' => HkTask::class,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return redirect()->back()
            ->with('success', "{$tasksCreated} task berhasil dibuat");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Display a listing of refunds.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        // Build query
        $query = Refund::where('property_id', $property->id)
            ->with(['roomStay.guest', 'roomStay.hotelRoom', 'processedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by refund method
        if ($request->filled('refund_method')) {
            $query->where('refund_method', $request->refund_method);
        }

        // Search by refund number or guest name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('refund_number', 'like', "%{$search}%")
                  ->orWhereHas('roomStay.guest', function ($gq) use ($search) {
                      $gq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Get refunds with pagination
        $refunds = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total' => Refund::where('property_id', $property->id)->count(),
            'pending' => Refund::where('property_id', $property->id)->pending()->count(),
            'processed' => Refund::where('property_id', $property->id)->processed()->count(),
            'total_amount' => Refund::where('property_id', $property->id)->sum('amount'),
        ];

        return view('refunds.index', compact('refunds', 'stats', 'property'));
    }

    /**
     * Show refund details.
     */
    public function show(Refund $refund)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($refund->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke refund ini.');
        }

        // Eager load relationships
        $refund->load([
            'roomStay.guest',
            'roomStay.hotelRoom',
            'roomStay.payments',
            'originalPayment',
            'processedBy',
        ]);

        return view('refunds.show', compact('refund', 'property'));
    }

    /**
     * Mark refund as processed.
     */
    public function process(Request $request, Refund $refund)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($refund->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke refund ini.');
        }

        // Check if already processed
        if ($refund->status === 'processed') {
            return redirect()->back()
                ->with('error', 'Refund ini sudah diproses sebelumnya.');
        }

        // Validate
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Mark as processed
            $refund->markAsProcessed($user->id);

            // Update notes if provided
            if (!empty($validated['notes'])) {
                $refund->update([
                    'notes' => ($refund->notes ? $refund->notes . "\n" : '') . $validated['notes'],
                ]);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => $user->name . " memproses refund {$refund->refund_number} sebesar Rp " . number_format($refund->amount, 0, ',', '.') . " untuk tamu {$refund->roomStay->guest->full_name}",
                'loggable_id' => $refund->id,
                'loggable_type' => Refund::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('refunds.show', $refund)
                ->with('success', 'Refund berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a refund.
     */
    public function cancel(Request $request, Refund $refund)
    {
        $user = auth()->user();
        $property = $user->property;

        // Validate property ownership
        if ($refund->property_id !== $property->id) {
            abort(403, 'Anda tidak memiliki akses ke refund ini.');
        }

        // Check if already processed
        if ($refund->status === 'processed') {
            return redirect()->back()
                ->with('error', 'Refund yang sudah diproses tidak dapat dibatalkan.');
        }

        // Validate
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Cancel refund
            $refund->cancel($validated['reason']);

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'action' => 'update',
                'description' => $user->name . " membatalkan refund {$refund->refund_number} dengan alasan: {$validated['reason']}",
                'loggable_id' => $refund->id,
                'loggable_type' => Refund::class,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('refunds.index')
                ->with('success', 'Refund berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membatalkan refund: ' . $e->getMessage());
        }
    }
}

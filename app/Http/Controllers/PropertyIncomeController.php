<?php

namespace App\Http\Controllers;

use App\Models\DailyIncome;
use App\Models\Property;
use App\Models\RoomStay;
use App\Http\Traits\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PropertyIncomesExport;
use Illuminate\Support\Str;
use App\Services\ReservationPriceService;
use App\Models\DailyOccupancy;
use App\Models\Reservation;
// TAMBAHKAN KEMBALI EVENT INI
use App\Events\OccupancyUpdated;
// TAMBAHKAN TRAIT
use App\Http\Traits\CalculatesBarPrices;

class PropertyIncomeController extends Controller
{
    // TAMBAHKAN TRAIT
    use LogActivity, CalculatesBarPrices;
    
    protected $priceService;

    public function __construct(ReservationPriceService $priceService)
    {
        $this->priceService = $priceService;
    }
    
    public function calendar()
    {
        $property = auth()->user()->property;
        return view('property.calendar.index', compact('property'));
    }
    
    public function getCalendarData(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            return response()->json(['events' => [], 'chartData' => []]);
        }

        // Fetch room stays with complete information
        // Only show active reservations and future bookings (exclude old checked-out stays)
        $roomStays = RoomStay::where('property_id', $property->id)
            ->where(function($query) {
                $query->whereIn('status', ['reserved', 'checked_in'])
                      ->orWhere(function($q) {
                          // Also show recent check-outs (last 7 days)
                          $q->where('status', 'checked_out')
                            ->where('check_out_date', '>=', now()->subDays(7));
                      });
            })
            ->with(['guest', 'hotelRoom.roomType'])
            ->orderBy('check_in_date', 'asc')
            ->get()
            ->map(function($roomStay) {
                // Determine color based on status
                $color = match($roomStay->status) {
                    'reserved' => '#3B82F6',      // Blue
                    'confirmed' => '#10B981',     // Green
                    'checked_in' => '#F59E0B',    // Amber/Orange
                    'checked_out' => '#6B7280',   // Gray
                    'cancelled' => '#EF4444',     // Red
                    'no_show' => '#9333EA',       // Purple
                    default => '#6B7280',
                };

                // Build title with complete information
                $guestName = $roomStay->guest
                    ? $roomStay->guest->full_name
                    : 'Guest';

                $roomNumber = $roomStay->hotelRoom
                    ? 'Room ' . $roomStay->hotelRoom->room_number
                    : '';

                $roomType = $roomStay->hotelRoom && $roomStay->hotelRoom->roomType
                    ? $roomStay->hotelRoom->roomType->name
                    : '';

                $status = ucfirst(str_replace('_', ' ', $roomStay->status));

                // Create detailed title
                $title = "{$guestName}";
                if ($roomNumber) {
                    $title .= " | {$roomNumber}";
                }

                // Create description for tooltip
                $description = "Guest: {$guestName}\n";
                if ($roomNumber) {
                    $description .= "Room: {$roomNumber}\n";
                }
                if ($roomType) {
                    $description .= "Type: {$roomType}\n";
                }
                $description .= "Confirmation: {$roomStay->confirmation_number}\n";
                $description .= "Status: {$status}\n";
                $description .= "Adults: {$roomStay->adults}" . ($roomStay->children > 0 ? ", Children: {$roomStay->children}" : "") . "\n";
                if ($roomStay->room_rate_per_night) {
                    $description .= "Rate: Rp " . number_format($roomStay->room_rate_per_night, 0, ',', '.') . "/night\n";
                }
                if ($roomStay->deposit_amount) {
                    $description .= "Deposit: Rp " . number_format($roomStay->deposit_amount, 0, ',', '.');
                }

                return [
                    'id' => $roomStay->id,
                    'title' => $title,
                    'start' => $roomStay->check_in_date->format('Y-m-d'),
                    // 🔧 BUG FIX: FullCalendar treats 'end' as exclusive, so add 1 day
                    // to properly display multi-day reservations as single event
                    'end' => $roomStay->check_out_date->addDay()->format('Y-m-d'),
                    'color' => $color,
                    'description' => $description,
                    'extendedProps' => [
                        'guestName' => $guestName,
                        'roomNumber' => $roomNumber,
                        'roomType' => $roomType,
                        'confirmationNumber' => $roomStay->confirmation_number,
                        'status' => $status,
                        'adults' => $roomStay->adults,
                        'children' => $roomStay->children,
                        'nights' => $roomStay->nights,
                        'roomRate' => $roomStay->room_rate_per_night ? 'Rp ' . number_format($roomStay->room_rate_per_night, 0, ',', '.') : '-',
                        'totalCharge' => $roomStay->total_room_charge ? 'Rp ' . number_format($roomStay->total_room_charge, 0, ',', '.') : '-',
                        'deposit' => $roomStay->deposit_amount ? 'Rp ' . number_format($roomStay->deposit_amount, 0, ',', '.') : '-',
                        'specialRequests' => $roomStay->special_requests ?? '-',
                    ],
                ];
            });

        $startDate = Carbon::now()->subDays(30);
        $chartOccupancyData = DailyOccupancy::where('property_id', $property->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'asc')
            ->get(['date', 'occupied_rooms']);

        $chartData = [
            'labels' => $chartOccupancyData->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d M')),
            'data' => $chartOccupancyData->pluck('occupied_rooms'),
        ];

        return response()->json([
            'events' => $roomStays,
            'chartData' => $chartData,
        ]);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat pada properti manapun.');
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $today = Carbon::today(); // Definisikan hari ini

        $incomesThisMonthQuery = DailyIncome::where('property_id', $property->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth]);

        $totalRevenue = (clone $incomesThisMonthQuery)->sum('total_revenue');
        $totalRoomRevenue = (clone $incomesThisMonthQuery)->sum('total_rooms_revenue');
        $totalFbRevenue = (clone $incomesThisMonthQuery)->sum('total_fb_revenue');
        $totalOthersIncome = (clone $incomesThisMonthQuery)->sum('others_income');

        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', $today->toDateString())
            ->first();

        $latestIncomes = $property->dailyIncomes()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // 🔧 BUG FIX: Add pending check-ins for property user dashboard
        $pendingCheckInToday = RoomStay::where('property_id', $property->id)
            ->pendingCheckInToday()
            ->with(['guest', 'hotelRoom'])
            ->orderBy('check_in_date')
            ->get();

        // --- DATA BARU: Reservasi Aktif / Mendatang ---
        $reservations = Reservation::where('property_id', $property->id)
            ->where('check_out_date', '>=', $today) // Ambil yang belum checkout
            ->orderBy('check_in_date', 'asc') // Urutkan dari yang paling dekat check-in
            ->limit(10) // Batasi 10 data untuk dashboard
            ->get();
            
        // --- DATA BARU: Riwayat Okupansi Manual ---
        $occupancyHistory = DailyOccupancy::where('property_id', $property->id)
            ->orderBy('date', 'desc') // Urutkan dari tanggal terbaru
            ->limit(5) // Ambil 5 data terakhir
            ->get();

        return view('property.dashboard', [
            'property' => $property,
            'totalRevenue' => $totalRevenue,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalFbRevenue' => $totalFbRevenue,
            'totalOthersIncome' => $totalOthersIncome,
            'occupancyToday' => $occupancyToday,
            'latestIncomes' => $latestIncomes,
            'pendingCheckInToday' => $pendingCheckInToday,  // 🔧 BUG FIX
            'reservations' => $reservations,
            'occupancyHistory' => $occupancyHistory,
        ]);
    }

    public function updateOccupancy(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;
    
        if (!$property) {
            return redirect()->back()->with('error', 'Akun Anda tidak terikat dengan properti manapun.');
        }
    
        $validated = $request->validate([
            'occupied_rooms' => 'required|integer|min:0',
            'date' => 'required|date_format:Y-m-d',
        ]);
    
        $manualRooms = $validated['occupied_rooms'];
    
        // 1. Ambil atau buat data okupansi harian
        $dailyOccupancy = DailyOccupancy::firstOrCreate(
            [
                'property_id' => $property->id,
                'date' => $validated['date'],
            ],
            [
                'occupied_rooms' => 0,
                'reservasi_ota' => 0,
                'reservasi_properti' => 0,
            ]
        );
    
        // 2. Hitung total okupansi baru (manual + ota)
        $dailyOccupancy->reservasi_properti = $manualRooms;
        $totalOccupiedRooms = $dailyOccupancy->reservasi_ota + $manualRooms; 
        $dailyOccupancy->occupied_rooms = $totalOccupiedRooms;
        $dailyOccupancy->save();
    
        // 3. Tentukan BAR Level (angka 1-5)
        $activeBarLevel = $this->getActiveBarLevel($totalOccupiedRooms, $property);

        // 4. Tentukan NAMA BAR (string "bar_1", "bar_2", dll)
        $newActiveBarName = $this->getActiveBarName($activeBarLevel);

        // 5. Set nilai 'bar_active' baru dan simpan
        $property->bar_active = $newActiveBarName;
        $property->save();

        // 6. SELALU KIRIM NOTIFIKASI setiap kali okupansi diupdate
        event(new OccupancyUpdated($property, $dailyOccupancy));

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'update',
            'description' => $user->name . " memperbarui data okupansi untuk tanggal {$validated['date']}, manual: {$manualRooms}, OTA: {$dailyOccupancy->reservasi_ota}, total kamar terisi: {$totalOccupiedRooms}, BAR aktif: {$newActiveBarName}",
            'loggable_id' => $dailyOccupancy->id,
            'loggable_type' => \App\Models\DailyOccupancy::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('property.dashboard')->with('success', 'Jumlah okupansi properti berhasil diperbarui.');
    }

    public function createOtaReservation()
    {
        $user = Auth::user();
        $property = $user->property;
        $sources = ['Traveloka', 'Booking.com', 'Agoda', 'Tiket.com'];

        return view('property.reservations.create', compact('property', 'sources'));
    }

    public function storeOtaReservation(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;

        $validated = $request->validate([
            'source' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'room_type_id' => 'required|exists:room_types,id',
        ]);

        $finalPrice = $this->priceService->getCurrentPricesForProperty($property->id, $validated['check_in_date'])
                                        ->firstWhere('name', $request->room_type_name)['price_ota'] ?? 0;

        $reservation = Reservation::create($validated + [
            'final_price' => $finalPrice,
            'property_id' => $property->id
        ]);

        // Calculate nights
        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'create',
            'description' => $user->name . " menambahkan reservasi OTA {$validated['source']} untuk tamu {$validated['guest_name']}, check-in: {$checkIn->format('d M Y')}, {$nights} malam, harga: Rp " . number_format($finalPrice, 0, ',', '.'),
            'loggable_id' => $reservation->id,
            'loggable_type' => \App\Models\Reservation::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('property.reservations.create')->with('success', 'Reservasi OTA berhasil ditambahkan.');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            return redirect('/')->with('error', 'Anda tidak terkait dengan properti manapun.');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = DailyIncome::where('property_id', $property->id);

        if ($startDate) {
            try {
                $query->whereDate('date', '>=', Carbon::parse($startDate));
            } catch (\Exception $e) {
                \Log::error('Failed to parse start date in PropertyIncomeController', [
                    'start_date' => $startDate,
                    'error' => $e->getMessage()
                ]);
            }
        }
        if ($endDate) {
            try {
                $query->whereDate('date', '<=', Carbon::parse($endDate));
            } catch (\Exception $e) {
                \Log::error('Failed to parse end date in PropertyIncomeController', [
                    'end_date' => $endDate,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $incomes = $query->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('property.income.index', compact('incomes', 'property', 'startDate', 'endDate'));
    }


    public function create()
    {
        $user = Auth::user();
        $property = $user->property;

        if (!$property) {
            return redirect('/')->with('error', 'Anda tidak terkait dengan properti manapun.');
        }

        return view('property.income.create', [
            'property' => $property,
            'date' => old('date', Carbon::today()->toDateString())
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $property = $user->property;
        if (!$property) {
            abort(403);
        }
    
        $validatedData = $request->validate([
            'date' => 'required|date|unique:daily_incomes,date,NULL,id,property_id,' . $property->id,
            'offline_rooms' => 'required|integer|min:0',
            'offline_room_income' => 'required|numeric|min:0',
            'online_rooms' => 'required|integer|min:0',
            'online_room_income' => 'required|numeric|min:0',
            'ta_rooms' => 'required|integer|min:0',
            'ta_income' => 'required|numeric|min:0',
            'gov_rooms' => 'required|integer|min:0',
            'gov_income' => 'required|numeric|min:0',
            'corp_rooms' => 'required|integer|min:0',
            'corp_income' => 'required|numeric|min:0',
            'compliment_rooms' => 'required|integer|min:0',
            'compliment_income' => 'required|numeric|min:0',
            'house_use_rooms' => 'required|integer|min:0',
            'house_use_income' => 'required|numeric|min:0',
            'afiliasi_rooms' => 'required|integer|min:0',
            'afiliasi_room_income' => 'required|numeric|min:0',
            'breakfast_income' => 'required|numeric|min:0',
            'lunch_income' => 'required|numeric|min:0',
            'dinner_income' => 'required|numeric|min:0',
            'others_income' => 'required|numeric|min:0',
        ], [
            'date.unique' => 'Pendapatan untuk tanggal ini sudah pernah dicatat.',
        ]);
    
        $incomeData = array_merge($validatedData, [
            'property_id' => $property->id,
            'user_id' => $user->id,
        ]);
    
        $income = DailyIncome::create($incomeData);

        $income->recalculateTotals();
        $income->save();

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'action' => 'create',
            'description' => $user->name . " mencatat pendapatan harian untuk tanggal {$income->date->format('d M Y')}, total kamar: {$income->total_rooms}, room revenue: Rp " . number_format($income->total_room_revenue, 0, ',', '.') . ", total revenue: Rp " . number_format($income->grand_total, 0, ',', '.'),
            'loggable_id' => $income->id,
            'loggable_type' => \App\Models\DailyIncome::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('property.income.index')->with('success', 'Pendapatan harian berhasil dicatat.');
    }

    public function edit(DailyIncome $income)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->property_id != $income->property_id) {
            abort(403, 'Akses tidak diizinkan untuk mengedit data ini.');
        }
        $property = $user->property;
        return view('property.income.edit', compact('income', 'property'));
    }
    
    public function update(Request $request, DailyIncome $income)
    {
        $user = Auth::user();
        $this->authorize('update', $income);
    
        $validatedData = $request->validate([
            'date' => 'required|date|unique:daily_incomes,date,' . $income->id . ',id,property_id,' . $income->property_id,
            'offline_rooms' => 'required|integer|min:0', 'offline_room_income' => 'required|numeric|min:0',
            'online_rooms' => 'required|integer|min:0', 'online_room_income' => 'required|numeric|min:0',
            'ta_rooms' => 'required|integer|min:0', 'ta_income' => 'required|numeric|min:0',
            'gov_rooms' => 'required|integer|min:0', 'gov_income' => 'required|numeric|min:0',
            'corp_rooms' => 'required|integer|min:0', 'corp_income' => 'required|numeric|min:0',
            'compliment_rooms' => 'required|integer|min:0', 'compliment_income' => 'required|numeric|min:0',
            'house_use_rooms' => 'required|integer|min:0', 'house_use_income' => 'required|numeric|min:0',
            'afiliasi_rooms' => 'required|integer|min:0', 'afiliasi_room_income' => 'required|numeric|min:0',
            'breakfast_income' => 'required|numeric|min:0',
            'lunch_income' => 'required|numeric|min:0',
            'dinner_income' => 'required|numeric|min:0',
            'others_income' => 'required|numeric|min:0',
        ], [
            'date.unique' => 'Pendapatan untuk tanggal ini sudah ada.',
        ]);
    
        // Simpan nilai lama untuk logging
        $oldTotalRooms = $income->total_rooms;
        $oldGrandTotal = $income->grand_total;

        $income->update($validatedData);

        $income->recalculateTotals();
        $income->save();

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $income->property_id,
            'action' => 'update',
            'description' => $user->name . " mengubah pendapatan harian untuk tanggal {$income->date->format('d M Y')}, total kamar: {$oldTotalRooms} → {$income->total_rooms}, total revenue: Rp " . number_format($oldGrandTotal, 0, ',', '.') . " → Rp " . number_format($income->grand_total, 0, ',', '.'),
            'loggable_id' => $income->id,
            'loggable_type' => \App\Models\DailyIncome::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($user->role === 'admin') {
            return redirect()->route('admin.properties.show', $income->property_id)->with('success', 'Data pendapatan berhasil diperbarui.');
        }

        return redirect()->route('property.income.index')->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(DailyIncome $income)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        $this->authorize('delete', $income);

        // Simpan data untuk logging sebelum delete
        $date = $income->date->format('d M Y');
        $totalRooms = $income->total_rooms;
        $grandTotal = $income->grand_total;
        $propertyId = $income->property_id;
        $incomeId = $income->id;

        $income->delete();

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $propertyId,
            'action' => 'delete',
            'description' => $user->name . " menghapus pendapatan harian untuk tanggal {$date}, total kamar: {$totalRooms}, total revenue: Rp " . number_format($grandTotal, 0, ',', '.'),
            'loggable_id' => $incomeId,
            'loggable_type' => \App\Models\DailyIncome::class,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($user->role === 'admin') {
            return back()->with('success', 'Data pendapatan untuk tanggal ' . $date . ' berhasil dihapus.');
        }

        return redirect()->route('property.income.index')->with('success', 'Data pendapatan untuk tanggal ' . $date . ' berhasil dihapus.');
    }

    public function exportIncomesExcel(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->property_id) {
            return redirect()->back()->with('error', 'Tidak dapat mengekspor data, properti tidak ditemukan.');
        }

        $propertyId = $user->property_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fileName = 'laporan_pendapatan_' . Str::slug($user->property->name) . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        // Get total for logging
        $query = \App\Models\DailyIncome::where('property_id', $propertyId);
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        $totalRevenue = $query->sum('grand_total');
        $recordCount = $query->count();

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $propertyId,
            'action' => 'export',
            'description' => $user->name . " mengekspor laporan pendapatan periode " . ($startDate ?? 'awal') . " s/d " . ($endDate ?? 'akhir') . " ke format Excel, total: {$recordCount} record, revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . ", file: {$fileName}",
            'loggable_id' => $user->property->id,
            'loggable_type' => \App\Models\Property::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(new PropertyIncomesExport($propertyId, $startDate, $endDate), $fileName);
    }

    public function exportIncomesCsv(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->property_id) {
            return redirect()->back()->with('error', 'Tidak dapat mengekspor data, properti tidak ditemukan.');
        }

        $propertyId = $user->property_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fileName = 'laporan_pendapatan_' . Str::slug($user->property->name) . '_' . Carbon::now()->format('Ymd_His') . '.csv';

        // Get total for logging
        $query = \App\Models\DailyIncome::where('property_id', $propertyId);
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        $totalRevenue = $query->sum('grand_total');
        $recordCount = $query->count();

        // Log activity dengan detail lengkap
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $propertyId,
            'action' => 'export',
            'description' => $user->name . " mengekspor laporan pendapatan periode " . ($startDate ?? 'awal') . " s/d " . ($endDate ?? 'akhir') . " ke format CSV, total: {$recordCount} record, revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . ", file: {$fileName}",
            'loggable_id' => $user->property->id,
            'loggable_type' => \App\Models\Property::class,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return Excel::download(new PropertyIncomesExport($propertyId, $startDate, $endDate), $fileName, \Maatwebsite\Excel\Excel::CSV);
    }
}
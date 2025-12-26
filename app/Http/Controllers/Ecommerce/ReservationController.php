<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\DailyOccupancy;
use App\Models\Property; // <-- INI ADALAH BARIS PERBAIKANNYA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use App\Http\Traits\CalculatesBarPrices;
use App\Models\RoomType;

class ReservationController extends Controller
{
    use CalculatesBarPrices;
    /**
     * Menampilkan daftar reservasi.
     */
    public function getActiveBarPrice(RoomType $roomType)
    {
        $property = $roomType->property;

        $occupancyToday = DailyOccupancy::where('property_id', $property->id)
            ->where('date', today()->toDateString())
            ->first();
            
        $occupiedRooms = $occupancyToday ? $occupancyToday->occupied_rooms : 0;
        
        $activeBarLevel = $this->getActiveBarLevel($occupiedRooms, $property);
        $activePrice = $this->calculateActiveBarPrice($roomType, $activeBarLevel);

        return response()->json(['price' => $activePrice]);
    }
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari request
        $search = $request->input('search');

        $query = Reservation::with('guest');

        // Peran 'ecommerce' bisa melihat semua reservasi.
        // Peran lain (jika ada yang mengakses) hanya melihat milik mereka.
        if (Auth::user()->role !== 'online_ecommerce') {
            $query->where('created_by', Auth::id());
        }

        // Terapkan filter pencarian jika ada input
        $query->when($search, function ($q, $search) {
            // Cari berdasarkan nama tamu melalui relasi
            return $q->whereHas('guest', function($guestQuery) use ($search) {
                $guestQuery->where('name', 'like', "%{$search}%");
            });
        });

        // Urutkan dan tampilkan dengan paginasi
        $reservations = $query->orderBy('check_in_date', 'desc')
            ->paginate(10)
            ->withQueryString(); // Agar paginasi tetap membawa query pencarian

        // Kirim data ke view
        return view('property.reservations.index', compact('reservations', 'search'));
    }

    /**
     * Menampilkan form untuk membuat reservasi baru.
     */
    public function create()
    {
        // Ambil properti yang terikat pada user
        $property = Auth::user()->property;

        if (!$property) {
            return redirect()->route('property.dashboard')->with('error', 'Akun Anda tidak terikat pada properti manapun.');
        }
        
        // Ambil tipe kamar yang sesuai dengan properti user
        $roomTypes = $property->roomTypes()->orderBy('name')->get();

        return view('property.reservations.create', compact('property', 'roomTypes'));
    }

    /**
     * Menyimpan reservasi baru ke database dan mengupdate okupansi.
     */
    public function store(Request $request)
    {
        $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'property_id' => 'required|integer|exists:properties,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'number_of_rooms' => 'required|integer|min:1',
            'final_price' => 'required|numeric|min:0',
        ]);

        // Find or create guest
        $guest = \App\Models\Guest::firstOrCreate(
            [
                'property_id' => $request->property_id,
                'email' => $request->guest_email,
            ],
            [
                'name' => $request->guest_name,
                'phone' => $request->guest_phone ?? '',
            ]
        );

        // Calculate nights
        $checkIn = \Carbon\Carbon::parse($request->check_in_date);
        $checkOut = \Carbon\Carbon::parse($request->check_out_date);
        $nights = $checkIn->diffInDays($checkOut);

        // Generate reservation number
        $property = \App\Models\Property::find($request->property_id);
        $reservationNumber = 'RSV-' . strtoupper(substr($property->name, 0, 3)) . '-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Create reservation
        $reservation = Reservation::create([
            'property_id' => $request->property_id,
            'guest_id' => $guest->id,
            'room_type_id' => $request->room_type_id,
            'reservation_number' => $reservationNumber,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'nights' => $nights,
            'adults' => 1,
            'children' => 0,
            'room_rate_per_night' => $request->final_price / $nights,
            'total_room_charge' => $request->final_price,
            'deposit_amount' => 0,
            'deposit_paid' => 0,
            'source' => 'website',
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        $this->updateDailyOccupancies($reservation, 'increment');

        return redirect()->route('property.reservations.index')
            ->with('success', 'Reservasi untuk ' . $request->guest_name . ' berhasil dibuat.');
    }
    
    public function getRoomTypesByProperty(Property $property)
    {
        // Pastikan hanya mengambil tipe kamar dari properti yang diminta
        $roomTypes = $property->roomTypes()->orderBy('name')->get(['id', 'name']);
        
        return response()->json($roomTypes);
    }

    /**
     * Menampilkan form untuk mengedit reservasi.
     */
   public function edit(Reservation $reservation)
    {
        // Pastikan pengguna hanya bisa mengedit reservasi propertinya
        if (Auth::user()->property_id != $reservation->property_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses reservasi ini.');
        }
    
        $property = Auth::user()->property;
        $roomTypes = $property->roomTypes()->orderBy('name')->get();
    
        // Tampilkan view 'edit' dengan data yang diperlukan
        return view('property.reservations.edit', compact('reservation', 'property', 'roomTypes'));
    }

    /**
     * Mengupdate reservasi di database dan menyesuaikan okupansi.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $oldReservationData = $reservation->replicate();

        $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after_or_equal:check_in_date',
            'property_id' => 'required|integer|exists:properties,id',
            'number_of_rooms' => 'required|integer|min:1',
            'final_price' => 'required|numeric|min:0',
        ]);

        // Update guest information
        $reservation->guest->update([
            'name' => $request->guest_name,
            'email' => $request->guest_email,
        ]);

        // Recalculate nights
        $checkIn = \Carbon\Carbon::parse($request->check_in_date);
        $checkOut = \Carbon\Carbon::parse($request->check_out_date);
        $nights = $checkIn->diffInDays($checkOut);

        // Update reservation
        $reservation->update([
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'nights' => $nights,
            'room_rate_per_night' => $request->final_price / $nights,
            'total_room_charge' => $request->final_price,
        ]);

        $this->updateDailyOccupancies($oldReservationData, 'decrement');
        $this->updateDailyOccupancies($reservation, 'increment');

        return redirect()->route('ecommerce.reservations.index')
            ->with('success', 'Reservasi untuk ' . $request->guest_name . ' berhasil diperbarui.');
    }

    /**
     * Menghapus reservasi dan mengurangi okupansi.
     */
    public function destroy(Reservation $reservation)
    {
        $this->updateDailyOccupancies($reservation, 'decrement');
        $reservation->delete();

        return redirect()->route('property.reservations.index')
        ->with('success', 'Reservasi berhasil dihapus.');
    }

    /**
     * Helper method untuk mengupdate data okupansi harian.
     */
    private function updateDailyOccupancies(Reservation $reservation, string $action)
    {
        // Ubah periode agar mencakup tanggal checkout
        $period = CarbonPeriod::create($reservation->check_in_date, $reservation->check_out_date);
        $rooms = 1; // Asumsi 1 kamar per reservasi (sesuai struktur baru)

        foreach ($period as $date) {
            $dailyOccupancy = DailyOccupancy::firstOrCreate(
                [
                    'property_id' => $reservation->property_id,
                    'date' => $date->toDateString(),
                ],
                [
                    'occupied_rooms' => 0,
                    'reservasi_ota' => 0,
                    'reservasi_properti' => 0,
                ]
            );

            if ($action === 'increment') {
                if (in_array($reservation->source, ['ota', 'website'])) {
                    $dailyOccupancy->increment('reservasi_ota', $rooms);
                } else {
                    $dailyOccupancy->increment('reservasi_properti', $rooms);
                }
                $dailyOccupancy->increment('occupied_rooms', $rooms);
            } else { // decrement
                if (in_array($reservation->source, ['ota', 'website'])) {
                    $dailyOccupancy->decrement('reservasi_ota', $rooms);
                } else {
                    $dailyOccupancy->decrement('reservasi_properti', $rooms);
                }
                $dailyOccupancy->decrement('occupied_rooms', $rooms);
            }
        }
    }
}
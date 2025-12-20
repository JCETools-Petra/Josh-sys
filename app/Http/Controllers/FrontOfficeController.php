<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\HotelRoom;
use App\Models\RoomStay;
use App\Models\Guest;
use App\Models\RoomType;
use App\Services\FrontOfficeService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FrontOfficeController extends Controller
{
    protected $frontOfficeService;

    public function __construct(FrontOfficeService $frontOfficeService)
    {
        $this->frontOfficeService = $frontOfficeService;
    }

    /**
     * Display Front Office dashboard.
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
        $occupiedRooms = $property->hotelRooms()->occupied()->count();
        $dirtyRooms = $property->hotelRooms()->dirty()->count();
        $maintenanceRooms = $property->hotelRooms()->needsMaintenance()->count();
        $availableRooms = $property->hotelRooms()->available()->with('roomType')->get();
        $availableRoomsCount = $availableRooms->count();

        // Get today's activities
        $checkingInToday = RoomStay::where('property_id', $property->id)
            ->checkingInToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        $checkingOutToday = RoomStay::where('property_id', $property->id)
            ->checkingOutToday()
            ->with(['guest', 'hotelRoom'])
            ->get();

        $currentGuests = RoomStay::where('property_id', $property->id)
            ->active()
            ->with(['guest', 'hotelRoom', 'roomType'])
            ->get();

        // Get room status overview
        $roomsByStatus = $property->hotelRooms()
            ->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('frontoffice.index', compact(
            'property',
            'totalRooms',
            'occupiedRooms',
            'dirtyRooms',
            'maintenanceRooms',
            'availableRooms',
            'availableRoomsCount',
            'checkingInToday',
            'checkingOutToday',
            'currentGuests',
            'roomsByStatus'
        ));
    }

    /**
     * Display room grid/rack.
     */
    public function roomGrid()
    {
        $user = auth()->user();
        $property = $user->property;

        if (!$property) {
            abort(403, 'Akun Anda tidak terikat dengan properti manapun.');
        }

        $rooms = $property->hotelRooms()
            ->with(['roomType.pricingRule', 'currentStay.guest', 'assignedHousekeeper'])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        // Get active BAR level
        $barActive = $property->bar_active ?? 'bar_1';

        // Get all room types with pricing rules for breakfast option lookup
        $roomTypes = $property->roomTypes()
            ->with('pricingRule')
            ->get();

        return view('frontoffice.room-grid', compact('property', 'rooms', 'barActive', 'roomTypes'));
    }


    /**
     * Create reservation for future dates.
     */
    public function createReservation(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'source' => 'required|in:walk_in,ota,ta,corporate,government,compliment,house_use,affiliate,online',
            'ota_name' => 'nullable|string',
            'ota_booking_id' => 'nullable|string',
            'special_requests' => 'nullable|string',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => 'required|in:ktp,passport,sim,other',
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string',
            'guest.city' => 'nullable|string',
        ]);

        try {
            // Calculate charges
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $totalRoomCharge * 0.10; // 10% tax
            $serviceCharge = $totalRoomCharge * 0.05; // 5% service charge

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'bar_level' => $barLevel,
                'status' => 'reserved', // Mark as reservation, not checked in yet
            ]);

            $roomStay = $this->frontOfficeService->createReservation($data);

            return redirect()->route('frontoffice.index')
                ->with('success', "Reservasi berhasil dibuat! Confirmation Number: {$roomStay->confirmation_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat reservasi: ' . $e->getMessage());
        }
    }

    /**
     * Process check-in.
     */
    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $property = $user->property;

        $validated = $request->validate([
            'hotel_room_id' => 'required|exists:hotel_rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_rate_per_night' => 'required|numeric|min:0',
            'with_breakfast' => 'required|boolean',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'source' => 'required|in:walk_in,ota,ta,corporate,government,compliment,house_use,affiliate,online',
            'ota_name' => 'nullable|string',
            'ota_booking_id' => 'nullable|string',
            'special_requests' => 'nullable|string',
            // Guest information
            'guest.first_name' => 'required|string|max:255',
            'guest.last_name' => 'nullable|string|max:255',
            'guest.email' => 'nullable|email',
            'guest.phone' => 'required|string|max:20',
            'guest.id_type' => 'required|in:ktp,passport,sim,other',
            'guest.id_number' => 'required|string|max:50',
            'guest.address' => 'nullable|string',
            'guest.city' => 'nullable|string',
        ]);

        try {
            // Calculate charges
            $room = HotelRoom::findOrFail($validated['hotel_room_id']);
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);

            $totalRoomCharge = $validated['room_rate_per_night'] * $nights;
            $taxAmount = $totalRoomCharge * 0.10; // 10% tax
            $serviceCharge = $totalRoomCharge * 0.05; // 5% service charge

            // Get current BAR level from property
            $barActive = $property->bar_active ?? 'bar_1';
            $barLevel = (int) str_replace('bar_', '', $barActive);

            $data = array_merge($validated, [
                'property_id' => $property->id,
                'total_room_charge' => $totalRoomCharge,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'bar_level' => $barLevel,
            ]);

            $roomStay = $this->frontOfficeService->checkIn($data);

            return redirect()->route('frontoffice.index')
                ->with('success', "Check-in berhasil! Confirmation Number: {$roomStay->confirmation_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan check-in: ' . $e->getMessage());
        }
    }

    /**
     * Process check-out.
     */
    public function checkOut(RoomStay $roomStay)
    {
        try {
            $this->frontOfficeService->checkOut($roomStay);

            return redirect()->route('frontoffice.index')
                ->with('success', "Check-out berhasil untuk kamar {$roomStay->hotelRoom->room_number}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal melakukan check-out: ' . $e->getMessage());
        }
    }

    /**
     * Show guest details.
     */
    public function showGuest(Guest $guest)
    {
        $guest->load(['roomStays.hotelRoom', 'roomStays.property', 'fnbOrders']);

        return view('frontoffice.guest-details', compact('guest'));
    }

    /**
     * Search for available rooms.
     */
    public function searchRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $user = auth()->user();
        $property = $user->property;

        $availableRooms = $this->frontOfficeService->getAvailableRooms(
            $property->id,
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        return response()->json([
            'success' => true,
            'rooms' => $availableRooms,
        ]);
    }
}

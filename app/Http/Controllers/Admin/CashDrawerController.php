<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashDrawer;
use App\Models\Property;
use App\Services\CashManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;

class CashDrawerController extends Controller
{
    protected $cashService;

    public function __construct(CashManagementService $cashService)
    {
        $this->cashService = $cashService;
    }

    /**
     * Get route prefix based on user role.
     * Front Office users use 'frontoffice' prefix, Admin/Owner use 'admin' prefix.
     */
    protected function getRoutePrefix(): string
    {
        return auth()->user()->role === 'pengguna_properti' ? 'frontoffice' : 'admin';
    }

    /**
     * Display cash drawer dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Authorization: pengguna_properti can only access their own property
        if ($user->role === 'pengguna_properti') {
            $propertyId = $user->property_id;
        } else {
            // Admin/Owner can access any property
            $propertyId = $request->get('property_id', $user->property_id);
        }

        // Get active drawer for the property
        $activeDrawer = $this->cashService->getActiveDrawer($propertyId);

        // Get recent closed drawers
        $recentDrawers = CashDrawer::where('property_id', $propertyId)
            ->where('status', 'closed')
            ->with(['openedBy', 'closedBy'])
            ->latest('closed_at')
            ->take(10)
            ->get();

        // Get drawer summary if active drawer exists
        $summary = null;
        if ($activeDrawer) {
            $summary = $this->cashService->getDrawerSummary($activeDrawer->id);
        }

        // Get all properties for filter
        $properties = Property::all();

        // Get route prefix for dynamic routing in views
        $routePrefix = $this->getRoutePrefix();

        return view('admin.cash-drawer.index', compact(
            'activeDrawer',
            'recentDrawers',
            'summary',
            'properties',
            'propertyId',
            'routePrefix'
        ));
    }

    /**
     * Show form to open new drawer.
     */
    public function create()
    {
        $user = auth()->user();
        $properties = Property::all();

        // Check if there's already an open drawer
        $activeDrawer = $this->cashService->getActiveDrawer($user->property_id);

        if ($activeDrawer) {
            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.cash-drawer.index")
                ->with('error', 'Sudah ada cash drawer yang terbuka. Tutup drawer terlebih dahulu.');
        }

        // Get route prefix for dynamic routing in views
        $routePrefix = $this->getRoutePrefix();

        return view('admin.cash-drawer.create', compact('properties', 'routePrefix'));
    }

    /**
     * Open a new cash drawer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'opening_balance' => 'required|numeric|min:0',
            'shift_type' => 'required|in:morning,afternoon,night,full_day',
            'opening_notes' => 'nullable|string|max:500',
        ]);

        try {
            $drawer = $this->cashService->openDrawer(
                $request->property_id,
                $request->opening_balance,
                $request->shift_type,
                $request->opening_notes
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $request->property_id,
                'action' => 'create',
                'description' => 'Membuka cash drawer dengan saldo awal Rp ' . number_format($request->opening_balance, 0, ',', '.'),
            ]);

            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.cash-drawer.index")
                ->with('success', 'Cash drawer berhasil dibuka dengan saldo awal Rp ' . number_format($request->opening_balance, 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show drawer detail with transactions.
     */
    public function show($id)
    {
        $drawer = $this->cashService->getDrawerWithTransactions($id);
        $summary = $this->cashService->getDrawerSummary($id);

        // Get route prefix for dynamic routing in views
        $routePrefix = $this->getRoutePrefix();

        return view('admin.cash-drawer.show', compact('drawer', 'summary', 'routePrefix'));
    }

    /**
     * Show form to close drawer.
     */
    public function edit($id)
    {
        $drawer = CashDrawer::findOrFail($id);

        if ($drawer->isClosed()) {
            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.cash-drawer.show", $id)
                ->with('error', 'Cash drawer sudah ditutup.');
        }

        $summary = $this->cashService->getDrawerSummary($id);

        // Get route prefix for dynamic routing in views
        $routePrefix = $this->getRoutePrefix();

        return view('admin.cash-drawer.close', compact('drawer', 'summary', 'routePrefix'));
    }

    /**
     * Close the cash drawer.
     */
    public function close(Request $request, $id)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        try {
            $drawer = $this->cashService->closeDrawer(
                $id,
                $request->closing_balance,
                $request->closing_notes
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $drawer->property_id,
                'action' => 'update',
                'description' => 'Menutup cash drawer dengan saldo akhir Rp ' . number_format($request->closing_balance, 0, ',', '.') .
                    ($drawer->variance != 0 ? ' (Variance: Rp ' . number_format($drawer->variance, 0, ',', '.') . ')' : ''),
            ]);

            $routePrefix = $this->getRoutePrefix();
            return redirect()->route("{$routePrefix}.cash-drawer.show", $id)
                ->with('success', 'Cash drawer berhasil ditutup.' .
                    ($drawer->variance != 0 ? ' Terdapat selisih Rp ' . number_format(abs($drawer->variance), 0, ',', '.') : ''));
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Add manual transaction (top-up, deposit to cashier, etc).
     */
    public function addTransaction(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'category' => 'required|in:top_up,deposit_to_cashier,adjustment,other',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
        ]);

        try {
            $drawer = CashDrawer::findOrFail($id);

            if ($drawer->isClosed()) {
                return back()->with('error', 'Tidak dapat menambah transaksi pada drawer yang sudah ditutup.');
            }

            $this->cashService->recordTransaction(
                $id,
                $request->type,
                $request->category,
                $request->amount,
                $request->description,
                null,
                null
            );

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $drawer->property_id,
                'action' => 'create',
                'description' => 'Menambah transaksi cash ' . ($request->type === 'in' ? 'masuk' : 'keluar') .
                    ' Rp ' . number_format($request->amount, 0, ',', '.') . ' - ' . $request->description,
            ]);

            return back()->with('success', 'Transaksi berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show cash drawer history/reports.
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        // Authorization: pengguna_properti can only access their own property
        if ($user->role === 'pengguna_properti') {
            $propertyId = $user->property_id;
        } else {
            // Admin/Owner can access any property
            $propertyId = $request->get('property_id', $user->property_id);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $drawers = $this->cashService->getDrawersByDateRange(
            $propertyId,
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        // Calculate totals
        $totalOpening = $drawers->sum('opening_balance');
        $totalClosing = $drawers->where('status', 'closed')->sum('closing_balance');
        $totalVariance = $drawers->where('status', 'closed')->sum('variance');

        $properties = Property::all();

        // Get route prefix for dynamic routing in views
        $routePrefix = $this->getRoutePrefix();

        return view('admin.cash-drawer.history', compact(
            'drawers',
            'properties',
            'propertyId',
            'startDate',
            'endDate',
            'totalOpening',
            'totalClosing',
            'totalVariance',
            'routePrefix'
        ));
    }
}

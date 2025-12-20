<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of API keys for a property
     */
    public function index(Property $property)
    {
        // Check authorization
        if (!$this->canManageProperty($property)) {
            abort(403, 'Anda tidak memiliki akses ke property ini.');
        }

        $apiKeys = $property->apiKeys()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.properties.api-keys.index', compact('property', 'apiKeys'));
    }

    /**
     * Show the form for creating a new API key
     */
    public function create(Property $property)
    {
        // Check authorization
        if (!$this->canManageProperty($property)) {
            abort(403, 'Anda tidak memiliki akses ke property ini.');
        }

        return view('admin.properties.api-keys.create', compact('property'));
    }

    /**
     * Store a newly created API key
     */
    public function store(Request $request, Property $property)
    {
        // Check authorization
        if (!$this->canManageProperty($property)) {
            abort(403, 'Anda tidak memiliki akses ke property ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'allowed_origins' => 'nullable|string',
        ]);

        $apiKey = ApiKey::create([
            'property_id' => $property->id,
            'name' => $validated['name'],
            'key' => ApiKey::generateKey(),
            'allowed_origins' => $validated['allowed_origins'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.properties.api-keys.show', ['property' => $property, 'apiKey' => $apiKey])
            ->with('success', 'API Key berhasil dibuat! Simpan API key ini dengan aman, tidak akan ditampilkan lagi.');
    }

    /**
     * Display the specified API key (only shown once after creation)
     */
    public function show(Property $property, ApiKey $apiKey)
    {
        // --- TEMPEL KODE INI UNTUK CEK DATA ---
        // Jika angka yang muncul berbeda, itulah penyebabnya.
        
        // --------------------------------------
    
        // Check authorization
        if (!$this->canManageProperty($property) || $apiKey->property_id != $property->id) {
            abort(403, 'Anda tidak memiliki akses ke API key ini.');
        }
    
        return view('admin.properties.api-keys.show', compact('property', 'apiKey'));
    }

    /**
     * Show the form for editing the API key
     */
    public function edit(Property $property, ApiKey $apiKey)
    {
        // Check authorization
        if (!$this->canManageProperty($property) || $apiKey->property_id != $property->id) {
            abort(403, 'Anda tidak memiliki akses ke API key ini.');
        }

        return view('admin.properties.api-keys.edit', compact('property', 'apiKey'));
    }

    /**
     * Update the specified API key
     */
    public function update(Request $request, Property $property, ApiKey $apiKey)
    {
        // Check authorization
        if (!$this->canManageProperty($property) || $apiKey->property_id != $property->id) {
            abort(403, 'Anda tidak memiliki akses ke API key ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'allowed_origins' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $apiKey->update($validated);

        return redirect()
            ->route('admin.properties.api-keys.index', $property)
            ->with('success', 'API Key berhasil diperbarui.');
    }

    /**
     * Remove the specified API key
     */
    public function destroy(Property $property, ApiKey $apiKey)
    {
        // Check authorization
        if (!$this->canManageProperty($property) || $apiKey->property_id != $property->id) {
            abort(403, 'Anda tidak memiliki akses ke API key ini.');
        }

        $apiKey->delete();

        return redirect()
            ->route('admin.properties.api-keys.index', $property)
            ->with('success', 'API Key berhasil dihapus.');
    }

    /**
     * Toggle the active status of an API key
     */
    public function toggle(Property $property, ApiKey $apiKey)
    {
        // Check authorization
        if (!$this->canManageProperty($property) || $apiKey->property_id != $property->id) {
            abort(403, 'Anda tidak memiliki akses ke API key ini.');
        }

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        $status = $apiKey->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.properties.api-keys.index', $property)
            ->with('success', "API Key berhasil {$status}.");
    }

    /**
     * Check if the current user can manage this property
     */
    private function canManageProperty(Property $property): bool
    {
        $user = Auth::user();

        // Admin can manage all properties
        if ($user->role === 'admin') {
            return true;
        }

        // Owner can manage their own property
        if (in_array($user->role, ['owner', 'pengurus']) && $user->property_id === $property->id) {
            return true;
        }

        return false;
    }
}

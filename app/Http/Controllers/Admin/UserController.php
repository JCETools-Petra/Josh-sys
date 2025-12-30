<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Daftar peran yang membutuhkan penugasan properti.
     * @var array
     */
    private $rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk', 'inventaris'];

    /**
     * Daftar semua peran yang tersedia di sistem.
     * @var array
     */
    private $allRoles = [
        'admin' => 'Admin',
        'owner' => 'Owner',
        'pengurus' => 'Pengurus',
        'pengguna_properti' => 'Pengguna Properti',
        'sales' => 'Sales',
        'inventaris' => 'Inventaris',
        'hk' => 'Housekeeping',
        'online_ecommerce' => 'E-Commerce',
    ];

    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index()
    {
        $users = User::with('property')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     */
    public function create()
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.users.create', [
            'properties' => $properties,
            'roles' => $this->allRoles
        ]);
    }

    /**
     * Menyimpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys($this->allRoles))],
            'property_id' => [
                Rule::requiredIf(in_array($request->input('role'), $this->rolesRequiringProperty)),
                'nullable',
                'exists:properties,id'
            ],
        ], [
            'property_id.required' => 'Properti wajib dipilih untuk peran ini.', // Pesan error kustom
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'property_id' => in_array($validated['role'], $this->rolesRequiringProperty) ? $validated['property_id'] : null,
            ]);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'property_id' => $user->property_id,
                'action' => 'create',
                'description' => 'Membuat pengguna baru: ' . $user->name . ' dengan role ' . $this->allRoles[$user->role] .
                                 ($user->property_id ? ' untuk properti ' . $user->property->name : ''),
                'loggable_type' => User::class,
                'loggable_id' => $user->id,
                'changes' => [
                    'name' => ['old' => null, 'new' => $user->name],
                    'email' => ['old' => null, 'new' => $user->email],
                    'role' => ['old' => null, 'new' => $user->role],
                    'property' => ['old' => null, 'new' => $user->property->name ?? '-'],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil dibuat.');

        } catch (\Exception $e) {
            // Jika ada error lain saat menyimpan
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
        }
    }

    /**
     * Mengarahkan ke halaman edit.
     */
    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    /**
     * Menampilkan form untuk mengedit pengguna.
     */
    public function edit(User $user)
    {
        $properties = Property::orderBy('name')->get();
        return view('admin.users.edit', [
            'user' => $user,
            'properties' => $properties,
            'roles' => $this->allRoles
        ]);
    }

    /**
     * Memperbarui data pengguna di database.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys($this->allRoles))],
            'property_id' => [
                Rule::requiredIf(in_array($request->input('role'), $this->rolesRequiringProperty)),
                'nullable',
                'exists:properties,id'
            ],
        ], [
            'property_id.required' => 'Properti wajib dipilih untuk peran ini.', // Pesan error kustom
        ]);

        try {
            // Track changes
            $changes = [];
            $oldProperty = $user->property;

            if ($user->name !== $validated['name']) {
                $changes['name'] = ['old' => $user->name, 'new' => $validated['name']];
            }
            if ($user->email !== $validated['email']) {
                $changes['email'] = ['old' => $user->email, 'new' => $validated['email']];
            }
            if ($user->role !== $validated['role']) {
                $changes['role'] = ['old' => $this->allRoles[$user->role], 'new' => $this->allRoles[$validated['role']]];
            }

            $newPropertyId = in_array($validated['role'], $this->rolesRequiringProperty) ? $validated['property_id'] : null;
            if ($user->property_id !== $newPropertyId) {
                $changes['property'] = [
                    'old' => $oldProperty->name ?? '-',
                    'new' => $newPropertyId ? Property::find($newPropertyId)->name : '-'
                ];
            }

            if (!empty($validated['password'])) {
                $changes['password'] = ['old' => '***', 'new' => '*** (diubah)'];
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            $user->property_id = $newPropertyId;

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            // Log activity
            if (!empty($changes)) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'property_id' => $user->property_id,
                    'action' => 'update',
                    'description' => 'Memperbarui data pengguna: ' . $user->name,
                    'loggable_type' => User::class,
                    'loggable_id' => $user->id,
                    'changes' => $changes,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.');
        }
    }

    /**
     * Memindahkan pengguna ke tempat sampah (soft delete).
     */
    public function destroy(User $user)
    {
        $this->authorize('manage-data');
        if ($user->id === 1) { // Melindungi super admin
            return redirect()->route('admin.users.index')->with('error', 'Super Admin tidak dapat dihapus.');
        }

        // Log activity before deletion
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $user->property_id,
            'action' => 'delete',
            'description' => 'Menghapus pengguna: ' . $user->name . ' (' . $user->email . ') dengan role ' . $this->allRoles[$user->role],
            'loggable_type' => User::class,
            'loggable_id' => $user->id,
            'changes' => [
                'status' => ['old' => 'active', 'new' => 'deleted'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dipindahkan ke sampah.');
    }
    
    /**
     * Menampilkan pengguna yang sudah di-soft delete.
     */
    public function trashed()
    {
        $users = User::onlyTrashed()->with('property')->latest()->paginate(10);
        return view('admin.users.trashed', compact('users'));
    }

    /**
     * Mengembalikan pengguna dari tempat sampah.
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $user->property_id,
            'action' => 'restore',
            'description' => 'Memulihkan pengguna: ' . $user->name . ' (' . $user->email . ')',
            'loggable_type' => User::class,
            'loggable_id' => $user->id,
            'changes' => [
                'status' => ['old' => 'deleted', 'new' => 'active'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $user->restore();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dipulihkan.');
    }

    /**
     * Menghapus pengguna secara permanen dari database.
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'property_id' => $user->property_id,
            'action' => 'force_delete',
            'description' => 'Menghapus PERMANEN pengguna: ' . $user->name . ' (' . $user->email . ')',
            'loggable_type' => User::class,
            'loggable_id' => $user->id,
            'changes' => [
                'status' => ['old' => 'deleted', 'new' => 'permanently deleted'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $user->forceDelete();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dihapus permanen.');
    }
}
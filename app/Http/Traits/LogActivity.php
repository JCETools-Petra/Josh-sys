<?php

namespace App\Http\Traits;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogActivity
{
    /**
     * Mencatat aktivitas pengguna ke dalam database.
     *
     * @param string $description Deskripsi dari aktivitas yang dilakukan.
     * @param Request $request Object request untuk mendapatkan IP & User Agent.
     * @param int|null $propertyId ID dari properti yang terkait dengan aktivitas.
     * @param string $action Action type: create, update, delete, etc.
     * @param int|null $loggableId ID dari model yang terkait.
     * @param string|null $loggableType Class name dari model yang terkait.
     */
    public function logActivity(
        string $description,
        Request $request,
        $propertyId = null,
        string $action = 'update',
        $loggableId = null,
        $loggableType = null
    ) {
        if (!Auth::check()) {
            return; // Jangan catat jika tidak ada user yang login
        }

        ActivityLog::create([
            'user_id'       => Auth::id(),
            'property_id'   => $propertyId,
            'action'        => $action,
            'description'   => $description,
            'loggable_id'   => $loggableId,
            'loggable_type' => $loggableType,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ]);
    }
}
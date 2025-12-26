<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\DailyIncome' => 'App\Policies\DailyIncomePolicy', // Pastikan policy ini ada jika digunakan
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate ini hanya akan memberikan izin 'true' jika peran pengguna
         * adalah 'admin'. Owner memiliki akses read-only saja.
         * Peran 'pengurus' dan lainnya akan mendapatkan 'false', sehingga hanya bisa melihat.
         */
        Gate::define('manage-data', function (User $user) {
            return $user->role === 'admin';
        });

        /**
         * Gate untuk memeriksa apakah user bisa melihat data (read-only access)
         * Owner dan Admin bisa melihat semua data
         */
        Gate::define('view-data', function (User $user) {
            return in_array($user->role, ['admin', 'owner']);
        });
    }
}
<?php

namespace App\Providers;

// Pastikan use ini ada
use App\Models\User;
use App\Models\Course;
use App\Policies\CoursePolicy;
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
        Course::class => CoursePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Kode Anda dimulai di sini, DI DALAM FUNGSI BOOT
        
        // Gate untuk Admin
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Gate untuk Teacher
        Gate::define('is-teacher', function (User $user) {
            return $user->role === 'teacher';
        });
    }
}
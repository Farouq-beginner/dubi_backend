<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Course;
use App\Policies\CoursePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Course::class => CoursePolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('is-teacher', function (User $user) {
            // Log::info(...); // Boleh tetap ada jika mau

            // dd(
            //     'INSIDE is-teacher GATE CHECK',
            //     'User ID:',
            //     $user->user_id,
            //     'User Role:',
            //     $user->role,
            //     'Gate will return:',
            //     ($user->role === 'teacher')
            // );

            return $user->role === 'teacher';
        });
    }
}
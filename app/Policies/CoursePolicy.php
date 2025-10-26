<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response; // Standard policy response handling (optional)
// use Illuminate\Support\Facades\Log; // For logging

class CoursePolicy
{
    /**
     * Determine whether the user can view any models.
     * Only allow admins for now.
     */
    public function viewAny(User $user): bool
    {
        // Example: Only allow admins to see a list of ALL courses
        // return $user->role === 'admin';
        return false; // Or keep it simple for now
    }

    /**
     * Determine whether the user can view the model.
     * Allow any authenticated user to view course details.
     */
    public function view(User $user, Course $course): bool
    {
        // Assuming any logged-in user can view any course detail
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Allow only teachers.
     */
    public function create(User $user): bool
    {
        return $user->role === 'teacher';
    }

    /**
     * Determine whether the user can update the model.
     * Only allow the user who created the course.
     */
    public function update(User $user, Course $course): bool
    {

        // This line won't be reached because dd() stops execution
        return $user->user_id === $course->created_by_user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only allow the user who created the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->user_id === $course->created_by_user_id;
    }

    /**
     * Determine whether the user can restore the model.
     * Not used if not using Soft Deletes.
     */
    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Not used if not using Soft Deletes.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }
}
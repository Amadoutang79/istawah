<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);

        // Gate optionnel basé sur permissions
        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });
    }
}
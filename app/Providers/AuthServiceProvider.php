<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        \App\Models\Attribute::class => \App\Policies\AttributePolicy::class,
        \App\Models\Project::class   => \App\Policies\ProjectPolicy::class,
        \App\Models\Timesheet::class => \App\Policies\TimesheetPolicy::class,
    ];

    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

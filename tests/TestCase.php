<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsUser(?User $user = null): User
    {
        $user ??= User::factory()->create();
        Passport::actingAs($user);
        return $user;
    }

    protected function actingAsAdmin(?User $user = null): User
    {
        $user ??= User::factory()->admin()->create();
        Passport::actingAs($user);
        return $user;
    }

    protected function actingAsManager(?User $user = null): User
    {
        $user ??= User::factory()->manager()->create();
        Passport::actingAs($user);
        return $user;
    }
}

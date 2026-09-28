<?php

declare(strict_types=1);

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('redirects guests to the login page', function (): void {
    $this->get('/')
        ->assertRedirect('/login');
});

it('returns a successful response for an authenticated user', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertSuccessful()
        ->assertSee(config('app.name'));
});

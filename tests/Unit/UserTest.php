<?php

declare(strict_types=1);

use App\Models\User;

it('builds initials from each word in the name', function (): void {
    $user = new User(['name' => 'Andres Crucitti']);

    expect($user->initials())->toBe('AC');
});

it('uses a single initial for a one-word name', function (): void {
    $user = new User(['name' => 'Madonna']);

    expect($user->initials())->toBe('M');
});

it('returns an empty string rather than erroring for an empty name', function (): void {
    $user = new User(['name' => '']);

    expect($user->initials())->toBe('');
});

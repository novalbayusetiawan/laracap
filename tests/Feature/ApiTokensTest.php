<?php

use App\Filament\Resources\ApiTokenResource\Pages\ManageApiTokens;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the api tokens resource page', function () {
    livewire(ManageApiTokens::class)
        ->assertSuccessful()
        ->assertSee('API Tokens');
});

it('can create a new api token that never expires', function () {
    livewire(ManageApiTokens::class)
        ->callAction('create', data: [
            'name' => 'Test Token',
            'never_expires' => true,
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    expect($this->user->tokens()->count())->toBe(1);
    
    $token = $this->user->tokens()->first();
    expect($token->name)->toBe('Test Token');
    expect($token->expires_at)->toBeNull();
});

it('can create a new api token with expiration date', function () {
    $expiresAt = now()->addDays(30);

    livewire(ManageApiTokens::class)
        ->callAction('create', data: [
            'name' => 'Expiring Token',
            'never_expires' => false,
            'expires_at' => $expiresAt->toDateTimeString(),
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    expect($this->user->tokens()->count())->toBe(1);
    $token = $this->user->tokens()->first();
    expect($token->name)->toBe('Expiring Token');
    expect($token->expires_at->toDateString())->toBe($expiresAt->toDateString());
});

it('can revoke an existing token', function () {
    $token = $this->user->createToken('Token To Revoke');
    
    expect($this->user->tokens()->count())->toBe(1);

    livewire(ManageApiTokens::class)
        ->callTableAction(DeleteAction::class, $token->accessToken)
        ->assertNotified();

    expect($this->user->tokens()->count())->toBe(0);
});

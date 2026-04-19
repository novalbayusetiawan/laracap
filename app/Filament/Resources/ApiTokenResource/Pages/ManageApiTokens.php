<?php

namespace App\Filament\Resources\ApiTokenResource\Pages;

use App\Filament\Resources\ApiTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ManageApiTokens extends ManageRecords
{
    protected static string $resource = ApiTokenResource::class;

    public string $plainTextToken = '';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Token')
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $expiresAt = isset($data['expires_at']) ? \Carbon\Carbon::parse($data['expires_at']) : null;
                    
                    $token = Auth::user()->createToken(
                        $data['name'], 
                        ['*'], 
                        $expiresAt
                    );

                    // Re-save the model with the plain text token (to satisfy the user's explicit requirement of being able to copy it anytime)
                    $tokenModel = $token->accessToken;
                    $tokenModel->plain_text_token = $token->plainTextToken;
                    $tokenModel->save();

                    return $tokenModel;
                }),
        ];
    }
}

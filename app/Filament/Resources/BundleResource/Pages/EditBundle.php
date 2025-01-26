<?php

namespace App\Filament\Resources\BundleResource\Pages;

use App\Filament\Resources\BundleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBundle extends EditRecord
{
    protected static string $resource = BundleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $filePath = storage_path('app/public/' . $data['file_path']);

        $data['size'] = filesize($filePath);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

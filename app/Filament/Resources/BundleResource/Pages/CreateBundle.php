<?php

namespace App\Filament\Resources\BundleResource\Pages;

use App\Filament\Resources\BundleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBundle extends CreateRecord
{
    protected static string $resource = BundleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $filePath = storage_path('app/public/' . $data['file_path']);

        $data['size'] = filesize($filePath);

        return $data;
    }
}

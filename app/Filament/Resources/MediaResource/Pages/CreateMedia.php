<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = Str::uuid()->toString();
        $data['filename'] = basename($data['path']);
        $data['original_name'] = $data['original_name'] ?? $data['filename'];
        $data['mime_type'] = $data['mime_type'] ?? 'application/octet-stream';
        $data['size'] = $data['size'] ?? 0;
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}

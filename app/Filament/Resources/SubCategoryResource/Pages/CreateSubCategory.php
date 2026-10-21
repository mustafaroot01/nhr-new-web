<?php

namespace App\Filament\Resources\SubCategoryResource\Pages;

use App\Filament\Resources\SubCategoryResource;
use App\Models\CategoryTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSubCategory extends CreateRecord
{
    protected static string $resource = SubCategoryResource::class;

    protected bool $slugWasAuto = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = Str::uuid()->toString();

        if (blank($data['slug'] ?? null)) {
            $data['slug'] = $data['uuid'];
            $this->slugWasAuto = true;
        }

        unset($data['translations']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $formData = $this->data;

        if ($this->slugWasAuto) {
            $this->record->update(['slug' => (string) $this->record->id]);
        }

        foreach (['ar', 'en'] as $locale) {
            $t = $formData['translations'][$locale] ?? [];
            if (! empty($t['name'])) {
                CategoryTranslation::create([
                    'category_id' => $this->record->id,
                    'locale' => $locale,
                    ...$t,
                ]);
            }
        }
    }
}

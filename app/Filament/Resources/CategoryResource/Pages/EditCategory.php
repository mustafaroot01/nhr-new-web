<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\CategoryTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $category = $this->record;
        $category->load('translations');

        $data['translations'] = [];
        foreach (['ar', 'en'] as $locale) {
            $t = $category->translation($locale);
            $data['translations'][$locale] = $t ? $t->toArray() : [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['translations']);

        return $data;
    }

    protected function afterSave(): void
    {
        $formData = $this->data;
        foreach (['ar', 'en'] as $locale) {
            $t = $formData['translations'][$locale] ?? [];
            if (! empty($t['name'])) {
                CategoryTranslation::updateOrCreate(
                    ['category_id' => $this->record->id, 'locale' => $locale],
                    $t,
                );
            }
        }
    }
}

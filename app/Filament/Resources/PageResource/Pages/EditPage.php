<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\PostTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $post = $this->record;
        $post->load('translations');

        $data['translations'] = [];
        foreach (['ar', 'en'] as $locale) {
            $t = $post->translation($locale);
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
        $post = $this->record;

        foreach (['ar', 'en'] as $locale) {
            $translation = $formData['translations'][$locale] ?? [];
            if (! empty($translation['title'])) {
                PostTranslation::updateOrCreate(
                    ['post_id' => $post->id, 'locale' => $locale],
                    $translation,
                );
            } else {
                PostTranslation::where('post_id', $post->id)->where('locale', $locale)->delete();
            }
        }
    }
}

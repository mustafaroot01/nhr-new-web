<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Post;
use App\Models\PostTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();
        $data['uuid'] = Str::uuid()->toString();
        $data['type'] = 'page';
        $data['published_at'] = now();
        unset($data['translations']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $formData = $this->data;
        $post = $this->record;

        foreach (['ar', 'en'] as $locale) {
            $translation = $formData['translations'][$locale] ?? [];
            if (! empty($translation['title'])) {
                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => $locale,
                    ...$translation,
                ]);
            }
        }
    }
}

<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Concerns\HandlesPostMedia;
use App\Models\PostTranslation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use HandlesPostMedia;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $post = $this->record;
        $post->load('translations', 'featuredImage', 'galleryMedia', 'attachments');

        $data['translations'] = [];
        foreach (['ar', 'en'] as $locale) {
            $t = $post->translation($locale);
            $data['translations'][$locale] = $t ? $t->toArray() : [];
        }

        $data['featured_image_path'] = $post->featuredImage?->path;
        $data['gallery'] = $post->galleryMedia->pluck('path')->toArray();
        $data['attachment_files'] = $post->attachments->pluck('path')->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['translations'], $data['featured_image_path'], $data['gallery'], $data['attachment_files']);

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

        $this->syncFeaturedImage($post, $formData['featured_image_path'] ?? null);
        $this->syncGallery($post, $formData['gallery'] ?? []);
        $this->syncAttachments($post, $formData['attachment_files'] ?? []);
    }
}

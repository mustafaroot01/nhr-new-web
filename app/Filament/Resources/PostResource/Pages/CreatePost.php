<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Concerns\HandlesPostMedia;
use App\Models\Post;
use App\Models\PostTranslation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePost extends CreateRecord
{
    use HandlesPostMedia;

    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();
        $data['uuid'] = Str::uuid()->toString();
        $data['type'] = 'post';

        // Empty slug → use the uuid temporarily, swapped for the post id in afterCreate.
        if (blank($data['slug'] ?? null)) {
            $data['slug'] = $data['uuid'];
            $this->slugWasAuto = true;
        }

        unset($data['translations'], $data['featured_image_path'], $data['gallery'], $data['attachment_files']);

        return $data;
    }

    protected bool $slugWasAuto = false;

    protected function afterCreate(): void
    {
        $formData = $this->data;
        $post = $this->record;

        if ($this->slugWasAuto) {
            $post->update(['slug' => (string) $post->id]);
        }

        $this->saveTranslations($post, $formData);
        $this->syncFeaturedImage($post, $formData['featured_image_path'] ?? null);
        $this->syncGallery($post, $formData['gallery'] ?? []);
        $this->syncAttachments($post, $formData['attachment_files'] ?? []);
    }

    private function saveTranslations(Post $post, array $data): void
    {
        foreach (['ar', 'en'] as $locale) {
            $translation = $data['translations'][$locale] ?? [];
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

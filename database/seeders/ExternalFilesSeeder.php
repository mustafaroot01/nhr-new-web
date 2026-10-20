<?php

namespace Database\Seeders;

use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ExternalFilesSeeder extends Seeder
{
    private string $host = 'nahriqorg.files.wordpress.com';

    private array $downloaded = [];

    private int $okCount = 0;

    private int $failCount = 0;

    public function run(): void
    {
        $translations = PostTranslation::where('body', 'like', '%files.wordpress.com%')->get();
        $this->command->info('ترجمات فيها روابط خارجية: ' . $translations->count());

        foreach ($translations as $translation) {
            $this->processTranslation($translation);
        }

        $this->command->info("ملفات حُمّلت بنجاح: {$this->okCount}");
        $this->command->info("ملفات فشلت: {$this->failCount}");
    }

    private function processTranslation(PostTranslation $translation): void
    {
        $body = $translation->body;

        $pattern = '#https?://' . preg_quote($this->host, '#') . '/([^"\'\s)]+)#i';

        $body = preg_replace_callback($pattern, function ($matches) {
            $fullUrl = $matches[0];
            $relPathWithQuery = $matches[1];
            $relPath = explode('?', $relPathWithQuery)[0];
            $relPath = urldecode($relPath);

            $newPath = $this->download($fullUrl, $relPath);

            return $newPath ? '/storage/' . $newPath : $fullUrl;
        }, $body);

        if ($body !== $translation->body) {
            $translation->update(['body' => $body]);
        }
    }

    private function download(string $url, string $relPath): ?string
    {
        if (isset($this->downloaded[$relPath])) {
            return $this->downloaded[$relPath];
        }

        $cleanUrl = explode('?', $url)[0];
        $newRel = 'media/posts/external/' . $relPath;
        $dest = storage_path('app/public/' . $newRel);

        if (File::exists($dest)) {
            $this->downloaded[$relPath] = $newRel;

            return $newRel;
        }

        File::ensureDirectoryExists(dirname($dest));

        try {
            $response = Http::timeout(30)->withOptions(['allow_redirects' => true])->get($cleanUrl);
            if ($response->successful() && strlen($response->body()) > 100) {
                File::put($dest, $response->body());
                $this->downloaded[$relPath] = $newRel;
                $this->okCount++;

                return $newRel;
            }
        } catch (\Throwable $e) {
            // fall through
        }

        $this->failCount++;

        return null;
    }
}

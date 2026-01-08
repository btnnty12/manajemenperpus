<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Buku;

class DownloadBukuCovers extends Command
{
    protected $signature = 'buku:covers:download {--force : Overwrite existing local covers}';

    protected $description = 'Download external buku cover images into storage and update the cover path';

    public function handle(): int
    {
        $books = Buku::whereNotNull('cover')->get();
        if ($books->isEmpty()) {
            $this->info('No books with cover to process.');
            return 0;
        }

        foreach ($books as $book) {
            $cover = $book->cover;
            if (! $cover) continue;

            // Only process absolute URLs
            if (! Str::startsWith($cover, ['http://', 'https://'])) {
                $this->line("Skipping non-remote cover for [{$book->id}] {$book->judul}");
                continue;
            }

            // Skip if already downloaded unless --force
            $filename = 'covers/' . $book->id . '_' . Str::slug($book->judul) . '.' . pathinfo(parse_url($cover, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (Storage::disk('public')->exists($filename) && ! $this->option('force')) {
                $this->line("Already downloaded: $filename");
                $book->cover = $filename;
                $book->save();
                continue;
            }

            try {
                $resp = Http::timeout(15)->get($cover);
                if (! $resp->successful()) {
                    $this->error("Failed to download: $cover (status {$resp->status()})");
                    continue;
                }

                Storage::disk('public')->put($filename, $resp->body());
                $book->cover = $filename;
                $book->save();
                $this->info("Downloaded [$book->id] -> $filename");
            } catch (\Exception $e) {
                $this->error("Error downloading $cover: " . $e->getMessage());
            }
        }

        $this->info('Done.');
        return 0;
    }
}

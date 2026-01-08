<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Buku;

class DownloadCoversCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_downloads_external_covers_and_updates_model()
    {
        Storage::fake('public');
        Http::fake([
            '*' => Http::response(file_get_contents(__DIR__ . '/fixtures/sample.jpg'), 200, ['Content-Type' => 'image/jpeg'])
        ]);

        // Seed buku data
        $this->seed(\Database\Seeders\bukuSeeder::class);

        // Find a book that had an external cover in dummyData (Python Book)
        $book = Buku::where('judul', 'Python Book')->first();
        $this->assertNotNull($book);
        $this->assertStringStartsWith('http', $book->cover, 'Precondition: cover must be remote URL');

        // Run artisan command
        $this->artisan('buku:covers:download')->assertExitCode(0);

        $book->refresh();

        $this->assertStringStartsWith('covers/', $book->cover);
        Storage::disk('public')->assertExists($book->cover);
    }
}

<?php

namespace Ajustatech\Core\Tests\Unit\Traits;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class HandlesFileUploadsHarness
{
    use HandlesFileUploads;

    public function store(
        UploadedFile $file,
        string $disk,
        string $directory,
        ?string $filename = null,
        ?string $visibility = null
    ): string {
        return $this->storeUploadedFile($file, $disk, $directory, $filename, $visibility);
    }

    public function delete(string $disk, string $path): bool
    {
        return $this->deleteUploadedFile($disk, $path);
    }

    public function cleanupTemp(UploadedFile $file): void
    {
        $this->deleteTemporaryUploadedFile($file);
    }

    public function validate(
        UploadedFile $file,
        string $field,
        int $maxSizeKb,
        array $allowedExtensions = [],
        array $allowedMimeTypes = [],
        bool $mustBeImage = false
    ): void {
        $this->validateUploadedFile($file, $field, $maxSizeKb, $allowedExtensions, $allowedMimeTypes, $mustBeImage);
    }

    public function extension(UploadedFile $file, array $allowedExtensions = [], string $field = 'file'): string
    {
        return $this->resolveUploadedFileExtension($file, $allowedExtensions, $field);
    }

    public function stream(string $disk, string $path, ?string $mimeType = null): Response
    {
        return $this->streamUploadedFile($disk, $path, $mimeType);
    }
}

class HandlesFileUploadsTest extends TestCase
{
    public function test_it_stores_any_uploaded_file_on_given_disk_and_directory(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('manual.pdf', 64, 'application/pdf');
        $harness = new HandlesFileUploadsHarness();

        $storedPath = $harness->store($file, 'public', 'docs', 'manual-final.pdf');

        $this->assertSame('docs/manual-final.pdf', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_it_deletes_uploaded_file_from_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('docs/to-delete.txt', 'content');
        $harness = new HandlesFileUploadsHarness();

        $deleted = $harness->delete('public', 'docs/to-delete.txt');

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing('docs/to-delete.txt');
    }

    public function test_it_deletes_only_the_current_livewire_temporary_uploaded_file(): void
    {
        Storage::fake('tmp-for-tests');
        $source = UploadedFile::fake()->image('diagram.png');
        $temporaryFilename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($source);
        $temporaryPath = 'livewire-tmp/' . $temporaryFilename;

        Storage::disk('tmp-for-tests')->put($temporaryPath, 'temporary-content');
        Storage::disk('tmp-for-tests')->put('livewire-tmp/keep-me.txt', 'keep');

        $temporaryFile = TemporaryUploadedFile::createFromLivewire($temporaryFilename);
        $harness = new HandlesFileUploadsHarness();

        $harness->cleanupTemp($temporaryFile);

        Storage::disk('tmp-for-tests')->assertMissing($temporaryPath);
        Storage::disk('tmp-for-tests')->assertExists('livewire-tmp/keep-me.txt');
    }

    public function test_it_validates_uploaded_file_rules(): void
    {
        $harness = new HandlesFileUploadsHarness();
        $file = UploadedFile::fake()->image('ok.png')->size(256);

        $harness->validate(
            file: $file,
            field: 'image',
            maxSizeKb: 1024,
            allowedExtensions: ['png', 'jpg'],
            allowedMimeTypes: ['image/png', 'image/jpeg'],
            mustBeImage: true
        );

        $this->assertTrue(true);
    }

    public function test_it_rejects_disallowed_extension(): void
    {
        $this->expectException(ValidationException::class);

        $harness = new HandlesFileUploadsHarness();
        $file = UploadedFile::fake()->create('report.pdf', 10, 'application/pdf');

        $harness->extension($file, ['png', 'jpg'], 'image');
    }

    public function test_it_streams_uploaded_file_content_with_headers(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('docs/stream.txt', 'stream-content');
        $harness = new HandlesFileUploadsHarness();

        $response = $harness->stream('public', 'docs/stream.txt', 'text/plain');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=31536000', $cacheControl);
        $this->assertStringContainsString('immutable', $cacheControl);
    }
}

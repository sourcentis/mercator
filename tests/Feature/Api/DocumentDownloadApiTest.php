<?php

use App\Models\Document;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);
    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    Passport::actingAs($this->user);

    // Stockage temporaire : ne jamais écrire dans le vrai storage/docs
    $this->storage = sys_get_temp_dir().'/mercator-docs-test-'.uniqid();
    $this->app->useStoragePath($this->storage);
});

afterEach(function () {
    array_map('unlink', glob($this->storage.'/docs/*'));
    @rmdir($this->storage.'/docs');
    @rmdir($this->storage);
});

function storeDocumentFile(Document $document, string $binary): string
{
    $dir = storage_path('docs');
    if (! is_dir($dir)) {
        mkdir($dir, 0750, true);
    }
    $path = $dir.'/'.$document->id;
    file_put_contents($path, $binary);

    return $path;
}

it('downloads an ODT document byte for byte with its OpenDocument MIME type', function () {
    // Binary payload with NUL bytes and high bytes, larger than one 8 KiB chunk
    $binary = "PK\x03\x04".random_bytes(20000);
    $document = Document::factory()->create([
        'filename' => 'rapport.odt',
        'mimetype' => 'application/vnd.oasis.opendocument.text',
        'size' => strlen($binary),
    ]);
    $this->path = storeDocumentFile($document, $binary);

    $response = $this->get("/api/documents/{$document->id}/download", ['Accept' => 'application/octet-stream']);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.oasis.opendocument.text')
        ->assertDownload('rapport.odt');
    expect($response->streamedContent())->toBe($binary);
});

it('downloads a PDF document byte for byte', function () {
    $binary = "%PDF-1.7\n".random_bytes(10000)."\n%%EOF";
    $document = Document::factory()->create([
        'filename' => 'doc.pdf',
        'mimetype' => 'application/pdf',
        'size' => strlen($binary),
    ]);
    $this->path = storeDocumentFile($document, $binary);

    $response = $this->get("/api/documents/{$document->id}/download");

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('doc.pdf');
    expect($response->streamedContent())->toBe($binary);
});

it('falls back to application/octet-stream for unknown MIME types', function () {
    $document = Document::factory()->create([
        'filename' => 'x.bin',
        'mimetype' => 'application/x-evil',
    ]);
    $this->path = storeDocumentFile($document, 'data');

    $this->get("/api/documents/{$document->id}/download")
        ->assertOk()
        ->assertHeader('Content-Type', 'application/octet-stream');
});

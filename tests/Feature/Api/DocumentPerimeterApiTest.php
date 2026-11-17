<?php

// Non-régression GHSA-j7gj-9c8m-83w7 (volet API) : document_show ne suffit plus pour lire un
// document rattaché à des objets, il faut pouvoir voir l'un d'eux (périmètre, cartographe).

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Document;
use App\Models\Perimeter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PerimeterSettings;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    foreach (['roleIdsCache', 'dbPermissionsCache', 'cartographerCache'] as $cache) {
        (new ReflectionProperty(Cartographer::class, $cache))->setValue(null, []);
    }

    // Stockage temporaire : ne jamais écrire dans le vrai storage/docs
    $this->storage = sys_get_temp_dir().'/mercator-docs-test-'.uniqid();
    mkdir($this->storage.'/docs', 0755, true);
    Passport::loadKeysFrom(storage_path());
    $this->app->useStoragePath($this->storage);

    $this->p1 = Perimeter::find(Perimeter::DEFAULT_ID);
    $this->p2 = Perimeter::factory()->create();

    // Auditeur de P1 : documents + applications, rien dans P2
    $role = Role::factory()->create(['title' => 'Auditor-P1', 'perimeter_id' => $this->p1->id]);
    $role->permissions()->sync(Permission::query()->whereIn('title', [
        'document_access', 'document_show', 'application_access', 'application_show',
    ])->pluck('id')->all());
    $this->auditor = User::factory()->create();
    $this->auditor->roles()->sync([$role->id]);

    $this->docP1 = Document::factory()->create(['filename' => 'p1.txt', 'mimetype' => 'text/plain']);
    $this->docP2 = Document::factory()->create(['filename' => 'p2.txt', 'mimetype' => 'text/plain']);
    $this->orphan = Document::factory()->create(['filename' => 'orphan.txt', 'mimetype' => 'text/plain']);

    foreach ([[$this->p1, $this->docP1], [$this->p2, $this->docP2]] as [$perimeter, $document]) {
        $application = Application::factory()->create();
        DB::table('applications')->where('id', $application->id)->update(['perimeter_id' => $perimeter->id]);
        $application->documents()->attach($document->id);
    }

    foreach ([$this->docP1, $this->docP2, $this->orphan] as $document) {
        file_put_contents(storage_path('docs/'.$document->id), $document->filename);
    }

    PerimeterSettings::setEnabled(true);
    Passport::actingAs($this->auditor);
});

afterEach(function () {
    array_map('unlink', glob($this->storage.'/docs/*'));
    rmdir($this->storage.'/docs');
    rmdir($this->storage);
});

test('downloads a document attached to an object of his perimeter', function () {
    $this->get("/api/documents/{$this->docP1->id}/download")->assertOk();
    $this->getJson("/api/documents/{$this->docP1->id}")->assertOk();
});

test('cannot read a document attached only to objects of another perimeter', function () {
    $this->get("/api/documents/{$this->docP2->id}/download")->assertForbidden();
    $this->getJson("/api/documents/{$this->docP2->id}")->assertForbidden();
});

test('a document attached to no object only requires document_show', function () {
    $this->get("/api/documents/{$this->orphan->id}/download")->assertOk();
});

test('the administrator reads documents of every perimeter', function () {
    Passport::actingAs(User::query()->where('login', 'admin@admin.com')->first());

    $this->get("/api/documents/{$this->docP2->id}/download")->assertOk();
});

test('the list only contains documents of his perimeter and documents attached to nothing', function () {
    $ids = collect($this->getJson('/api/documents')->assertOk()->json())->pluck('id')->all();

    expect($ids)->toContain($this->docP1->id, $this->orphan->id)
        ->not->toContain($this->docP2->id);
});

test('the administrator lists every document', function () {
    Passport::actingAs(User::query()->where('login', 'admin@admin.com')->first());

    $ids = collect($this->getJson('/api/documents')->assertOk()->json())->pluck('id')->all();

    expect($ids)->toContain($this->docP1->id, $this->docP2->id, $this->orphan->id);
});

test('mass update cannot reach a document of another perimeter', function () {
    $role = $this->auditor->roles()->first();
    $role->permissions()->attach(Permission::query()->where('title', 'document_edit')->value('id'));

    $this->putJson('/api/documents/mass-update', ['items' => [['id' => $this->docP2->id, 'filename' => 'pwned.txt']]])
        ->assertNotFound();

    expect($this->docP2->fresh()->filename)->toBe('p2.txt');
});

test('cannot update or delete a document of another perimeter', function () {
    $role = $this->auditor->roles()->first();
    $role->permissions()->attach(Permission::query()->whereIn('title', ['document_edit', 'document_delete'])->pluck('id'));

    $this->putJson("/api/documents/{$this->docP2->id}", ['filename' => 'pwned.txt'])->assertForbidden();
    $this->deleteJson("/api/documents/{$this->docP2->id}")->assertForbidden();
    $this->deleteJson('/api/documents/mass-destroy', ['ids' => [$this->docP2->id]]);

    expect($this->docP2->fresh())->not->toBeNull()
        ->and(file_exists(storage_path('docs/'.$this->docP2->id)))->toBeTrue();
});

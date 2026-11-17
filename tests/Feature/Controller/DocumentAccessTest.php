<?php

// Non-régression GHSA-j7gj-9c8m-83w7 : GET /admin/documents/{id} ne vérifiait aucune
// autorisation, tout utilisateur authentifié pouvait télécharger n'importe quel document
// en itérant sur son identifiant.

use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Document;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();

    $this->application = Application::factory()->create();
    $this->document = Document::factory()->create(['filename' => 'secret.txt', 'mimetype' => 'text/plain']);
    $this->application->documents()->attach($this->document->id);

    // Stockage temporaire : ne jamais écrire dans le vrai storage/docs
    $this->storage = sys_get_temp_dir().'/mercator-docs-test-'.uniqid();
    mkdir($this->storage.'/docs', 0755, true);
    $this->app->useStoragePath($this->storage);
    file_put_contents(storage_path('docs/'.$this->document->id), 'confidential');
});

afterEach(function () {
    array_map('unlink', glob($this->storage.'/docs/*'));
    rmdir($this->storage.'/docs');
    rmdir($this->storage);
});

function documentAccessUser(array $titles): User
{
    $role = Role::query()->create(['title' => 'Restricted', 'perimeter_id' => 1]);
    $role->permissions()->sync(Permission::query()->whereIn('title', $titles)->pluck('id')->all());

    $user = User::factory()->create();
    $user->roles()->sync([$role->id]);

    return $user;
}

test('a user without access to the owning object cannot download the document', function () {
    $this->actingAs(documentAccessUser(['entity_access', 'entity_show']))
        ->get(route('admin.documents.show', $this->document->id))
        ->assertForbidden();
});

test('a user who can see the owning object can download the document', function () {
    $this->actingAs(documentAccessUser(['application_access', 'application_show']))
        ->get(route('admin.documents.show', $this->document->id))
        ->assertOk();
});

test('the administrator can download the document', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.documents.show', $this->document->id))
        ->assertOk();
});

test('an orphan document is only accessible from the session that uploaded it', function () {
    $orphan = Document::factory()->create();
    $user = documentAccessUser(['application_access', 'application_show']);

    $this->actingAs($user)
        ->get(route('admin.documents.show', $orphan->id))
        ->assertForbidden();

    $this->actingAs($user)
        ->withSession(['documents' => [$orphan->id]])
        ->get(route('admin.documents.show', $orphan->id))
        ->assertNotFound(); // autorisé, mais aucun fichier sur disque

    file_put_contents(storage_path('docs/'.$orphan->id), 'draft');
    $this->actingAs($user)
        ->withSession(['documents' => [$orphan->id]])
        ->get(route('admin.documents.show', $orphan->id))
        ->assertOk();
});

// Issue #2748 : les icônes personnalisées ne s'affichaient sur les cartes qu'avec la permission « configure ».
test('a custom icon is visible without the configure permission', function () {
    $icon = Document::factory()->create(['filename' => 'icon.png', 'mimetype' => 'image/png']);
    file_put_contents(storage_path('docs/'.$icon->id), 'png');
    Application::factory()->create(['icon_id' => $icon->id]);

    $this->actingAs(documentAccessUser(['entity_access', 'entity_show']))
        ->get(route('admin.documents.show', $icon->id))
        ->assertOk();
});

test('a non-image document referenced as icon stays protected', function () {
    $fake = Document::factory()->create(['filename' => 'secret.pdf', 'mimetype' => 'application/pdf']);
    file_put_contents(storage_path('docs/'.$fake->id), 'pdf');
    Application::factory()->create(['icon_id' => $fake->id]);

    $this->actingAs(documentAccessUser(['entity_access', 'entity_show']))
        ->get(route('admin.documents.show', $fake->id))
        ->assertForbidden();
});

test('a user cannot delete a document outside of the object being edited', function () {
    $this->actingAs(documentAccessUser(['application_access', 'application_show']))
        ->get(route('admin.documents.delete', $this->document->id))
        ->assertForbidden();

    expect(Document::query()->find($this->document->id))->not->toBeNull();
});

test('document configuration pages require the configure permission', function () {
    $user = documentAccessUser(['application_access']);

    $this->actingAs($user)->get(route('admin.config.documents'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.config.documents.check'))->assertForbidden();
});

// Non-régression GHSA-f6g2-8x59-5xjq : POST /admin/documents/store ne vérifiait ni les droits
// ni la taille du fichier, un utilisateur en lecture seule pouvait remplir le disque.

test('a read-only user cannot upload a document', function () {
    $count = Document::query()->count();

    $this->actingAs(documentAccessUser(['application_access', 'application_show']))
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertForbidden();

    expect(Document::query()->count())->toBe($count);
});

test('the auditor cannot upload a document', function () {
    $auditor = User::factory()->create();
    $auditor->roles()->sync([Role::query()->where('title', 'Auditor')->value('id')]);

    $this->actingAs($auditor)
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertForbidden();
});

test('a user with a create permission can upload a document', function () {
    $this->actingAs(documentAccessUser(['application_access', 'application_create']))
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertOk()
        ->assertJsonStructure(['success', 'id']);
});

test('a user with an edit permission can upload a document', function () {
    $response = $this->actingAs(documentAccessUser(['application_access', 'application_edit']))
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertOk();

    expect(file_exists(storage_path('docs/'.$response->json('id'))))->toBeTrue();
});

test('a cartographer without edit permission can upload a document', function () {
    $user = documentAccessUser(['application_access', 'application_show']);
    Cartographer::query()->create([
        'user_id' => $user->id,
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $this->application->id,
    ]);

    $this->actingAs($user)
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('doc.pdf', 10)])
        ->assertOk();
});

test('an uploaded document larger than 50 MB is rejected', function () {
    $count = Document::query()->count();

    $this->actingAs($this->admin)
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('big.bin', 51201)])
        ->assertUnprocessable();

    $this->actingAs($this->admin)
        ->post(route('admin.documents.store'), ['file' => UploadedFile::fake()->create('ok.bin', 51200)])
        ->assertOk();

    expect(Document::query()->count())->toBe($count + 1);
});

test('an upload without file is rejected', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.documents.store'))
        ->assertUnprocessable();
});

<?php

// Les icônes personnalisées des objets sont stockées comme documents (IconUploadService).
// Suite de GHSA-f6g2-8x59-5xjq : le fichier d'icône n'était validé (type, taille) que pour
// 5 modèles sur 17, et iconSelect acceptait n'importe quel identifiant de document, ce qui
// rendait public (Document::isIcon) une image attachée à un objet invisible pour l'utilisateur.

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Models\Application;
use App\Models\Building;
use App\Models\Document;
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

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());

    // Stockage temporaire : ne jamais écrire dans le vrai storage/docs
    $this->storage = sys_get_temp_dir().'/mercator-icons-test-'.uniqid();
    mkdir($this->storage.'/docs', 0755, true);
    $this->app->useStoragePath($this->storage);
});

afterEach(function () {
    array_map('unlink', glob($this->storage.'/docs/*'));
    rmdir($this->storage.'/docs');
    rmdir($this->storage);
});

test('every request handling an icon validates it', function () {
    $requests = [];
    foreach (glob(app_path('Http/Controllers/Admin/*.php')) as $controller) {
        $source = file_get_contents($controller);
        if (! str_contains($source, 'iconUploadService->handle')) {
            continue;
        }
        preg_match_all('/\b((?:Store|Update)\w+Request)\b/', $source, $matches);
        $requests = array_merge($requests, $matches[1]);
    }

    expect($requests)->not->toBeEmpty();
    foreach (array_unique($requests) as $request) {
        expect(class_uses_recursive('App\\Http\\Requests\\'.$request))
            ->toContain(ValidatesIcon::class);
    }
});

test('a non-PNG icon file is rejected', function () {
    $this->post(route('admin.buildings.store'), [
        'name' => 'Building A',
        'iconFile' => UploadedFile::fake()->create('icon.php', 10, 'application/x-php'),
    ])->assertSessionHasErrors('iconFile');

    expect(Building::query()->where('name', 'Building A')->exists())->toBeFalse()
        ->and(Document::query()->count())->toBe(0);
});

test('an icon file larger than 50 MB is rejected', function () {
    $this->post(route('admin.buildings.store'), [
        'name' => 'Building A',
        'iconFile' => UploadedFile::fake()->image('icon.png')->size(51201),
    ])->assertSessionHasErrors('iconFile');

    expect(Building::query()->where('name', 'Building A')->exists())->toBeFalse();
});

test('a valid PNG icon is stored as a document', function () {
    $this->post(route('admin.buildings.store'), [
        'name' => 'Building A',
        'iconFile' => UploadedFile::fake()->image('icon.png'),
    ])->assertSessionHasNoErrors();

    $building = Building::query()->where('name', 'Building A')->firstOrFail();

    expect($building->icon_id)->not->toBeNull()
        ->and(file_exists(storage_path('docs/'.$building->icon_id)))->toBeTrue();
});

test('a private image cannot be selected as icon', function () {
    // Image attachée à une application : pas une icône, donc pas publique
    $private = Document::factory()->create(['filename' => 'plan.png', 'mimetype' => 'image/png']);
    Application::factory()->create()->documents()->attach($private->id);

    $building = Building::factory()->create();

    $this->put(route('admin.buildings.update', $building), [
        'name' => $building->name,
        'iconSelect' => (string) $private->id,
    ])->assertSessionHasErrors('iconSelect');

    expect($building->fresh()->icon_id)->toBeNull()
        ->and($private->fresh()->isIcon())->toBeFalse();
});

test('an unknown document cannot be selected as icon', function () {
    $building = Building::factory()->create();

    $this->put(route('admin.buildings.update', $building), [
        'name' => $building->name,
        'iconSelect' => '999999',
    ])->assertSessionHasErrors('iconSelect');
});

test('an existing icon can be selected', function () {
    $icon = Document::factory()->create(['filename' => 'icon.png', 'mimetype' => 'image/png']);
    // Noms fixes : BUILDING_NAME n'offre que 26 valeurs, deux tirages identiques
    // feraient échouer la mise à jour sur la règle d'unicité du nom.
    Building::factory()->create(['name' => 'Batiment A', 'icon_id' => $icon->id]);

    $building = Building::factory()->create(['name' => 'Batiment B']);

    $this->put(route('admin.buildings.update', $building), [
        'name' => $building->name,
        'iconSelect' => (string) $icon->id,
    ])->assertSessionHasNoErrors();

    expect($building->fresh()->icon_id)->toBe($icon->id);
});

test('the current icon of the object can be kept', function () {
    // Icône historique non-image : n'est pas une icône au sens de isIcon(), mais reste conservable
    $legacy = Document::factory()->create(['filename' => 'icon.bin', 'mimetype' => 'application/octet-stream']);
    $building = Building::factory()->create(['icon_id' => $legacy->id]);

    $this->put(route('admin.buildings.update', $building), [
        'name' => $building->name,
        'iconSelect' => (string) $legacy->id,
    ])->assertSessionHasNoErrors();

    expect($building->fresh()->icon_id)->toBe($legacy->id);
});

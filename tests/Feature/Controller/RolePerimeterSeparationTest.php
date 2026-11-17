<?php

use App\Models\Cartographer;
use App\Models\Entity;
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
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

/*
 * Séparation des rôles par périmètre : un rôle ne confère ses permissions que dans son propre
 * périmètre. Scénario de référence — Alice a deux rôles :
 *   - « Writer-P1 » : entity_access/show/create/edit/delete dans le périmètre 1 ;
 *   - « Reader-P2 » : entity_access/show dans le périmètre 2 ;
 * et n'a aucun rôle dans le périmètre 3.
 */

uses(RefreshDatabase::class);

function permissionIds(array $titles): array
{
    return Permission::query()->whereIn('title', $titles)->pluck('id')->all();
}

function roleIn(int $perimeterId, string $title, array $permissions): Role
{
    $role = Role::factory()->create(['title' => $title, 'perimeter_id' => $perimeterId]);
    $role->permissions()->sync(permissionIds($permissions));

    return $role;
}

function entityIn(int $perimeterId): Entity
{
    $entity = Entity::factory()->create();
    DB::table('entities')->where('id', $entity->id)->update(['perimeter_id' => $perimeterId]);

    return $entity->fresh();
}

function entityDeleted(Entity $entity): bool
{
    return DB::table('entities')->where('id', $entity->id)->whereNotNull('deleted_at')->exists();
}

function entityNameInDb(Entity $entity): string
{
    return DB::table('entities')->where('id', $entity->id)->value('name');
}

function entityPerimeterInDb(Entity $entity): int
{
    return (int) DB::table('entities')->where('id', $entity->id)->value('perimeter_id');
}

/** @param list<int> $expected */
function expectIds($actual, array $expected): void
{
    expect(collect($actual)->sort()->values()->all())->toBe(collect($expected)->sort()->values()->all());
}

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    // Les caches statiques de Cartographer survivraient d'un test à l'autre (ids réutilisables).
    foreach (['roleIdsCache', 'dbPermissionsCache', 'cartographerCache'] as $cache) {
        $property = new ReflectionProperty(Cartographer::class, $cache);
        $property->setValue(null, []);
    }

    $this->p1 = Perimeter::find(Perimeter::DEFAULT_ID);
    $this->p2 = Perimeter::factory()->create();
    $this->p3 = Perimeter::factory()->create();

    $this->writerP1 = roleIn($this->p1->id, 'Writer-P1', ['entity_access', 'entity_show', 'entity_create', 'entity_edit', 'entity_delete']);
    $this->readerP2 = roleIn($this->p2->id, 'Reader-P2', ['entity_access', 'entity_show']);

    $this->alice = User::factory()->create();
    $this->alice->roles()->attach([$this->writerP1->id, $this->readerP2->id]);

    $this->e1 = entityIn($this->p1->id);
    $this->e2 = entityIn($this->p2->id);
    $this->e3 = entityIn($this->p3->id);

    // isAdmin() teste l'id de rôle 1 en dur, or l'auto-incrément MySQL dérive d'un test à l'autre
    // (le rôle « Admin » seedé n'a plus l'id 1 après le premier test) : on garantit un vrai admin.
    if (! Role::query()->whereKey(1)->exists()) {
        DB::table('roles')->insert(['id' => 1, 'title' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);
    }
    Role::query()->findOrFail(1)->permissions()->sync(Permission::query()->pluck('id')->all());
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach(1);

    PerimeterSettings::setEnabled(true);
});

describe('permissions stored per perimeter', function () {
    test('each role only carries its permissions in its own perimeter', function () {
        $map = $this->alice->permissionsByPerimeter();

        expect(array_keys($map))->toEqualCanonicalizing([$this->p1->id, $this->p2->id])
            ->and($map[$this->p1->id])->toContain('entity_edit', 'entity_delete')
            ->and($map[$this->p2->id])->toContain('entity_access', 'entity_show')
            ->and($map[$this->p2->id])->not->toContain('entity_edit')
            ->and($map[$this->p2->id])->not->toContain('entity_delete');
    });

    test('two roles in the same perimeter are merged', function () {
        $extra = roleIn($this->p2->id, 'Editor-P2', ['entity_edit']);
        $this->alice->roles()->attach($extra);

        expect($this->alice->fresh()->permissionsByPerimeter()[$this->p2->id])
            ->toContain('entity_access', 'entity_edit');
    });

    test('the session payload keeps the per-perimeter map next to the flat union', function () {
        $data = $this->alice->sessionPermissionData();

        expect($data['auth_permissions_by_perimeter'])->toBe($this->alice->permissionsByPerimeter())
            ->and($data['auth_permissions'])->toContain('entity_edit', 'entity_access')
            ->and($data['auth_role_ids'])->toEqualCanonicalizing([$this->writerP1->id, $this->readerP2->id]);
    });
});

describe('Gate decisions', function () {
    test('an object-level ability is decided in the perimeter of the object', function () {
        $gate = Gate::forUser($this->alice);

        expect($gate->allows('entity_edit', $this->e1))->toBeTrue()
            ->and($gate->allows('entity_delete', $this->e1))->toBeTrue()
            // Reader in P2: read yes, write no — even though Writer-P1 grants entity_edit elsewhere.
            ->and($gate->allows('entity_show', $this->e2))->toBeTrue()
            ->and($gate->allows('entity_edit', $this->e2))->toBeFalse()
            ->and($gate->allows('entity_delete', $this->e2))->toBeFalse()
            // No role at all in P3.
            ->and($gate->allows('entity_show', $this->e3))->toBeFalse()
            ->and($gate->allows('entity_access', $this->e3))->toBeFalse();
    });

    test('edit-object / show-object follow the same per-perimeter rule', function () {
        $gate = Gate::forUser($this->alice);

        expect($gate->allows('edit-object', $this->e1))->toBeTrue()
            ->and($gate->allows('edit-object', $this->e2))->toBeFalse()
            ->and($gate->allows('show-object', $this->e2))->toBeTrue()
            ->and($gate->allows('show-object', $this->e3))->toBeFalse();
    });

    test('a class-level ability follows the active perimeter', function () {
        $gate = Gate::forUser($this->alice);

        session(['active_perimeter' => $this->p1->id]);
        expect($gate->allows('entity_edit'))->toBeTrue()
            ->and($gate->allows('entity_create'))->toBeTrue();

        session(['active_perimeter' => $this->p2->id]);
        expect($gate->allows('entity_access'))->toBeTrue()
            ->and($gate->allows('entity_edit'))->toBeFalse()
            ->and($gate->allows('entity_create'))->toBeFalse();
    });

    test('with "tous" selected a class-level ability holds if any perimeter grants it', function () {
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expect(Gate::forUser($this->alice)->allows('entity_create'))->toBeTrue();
    });

    test('a permission absent everywhere stays denied', function () {
        expect(Gate::forUser($this->alice)->allows('application_access'))->toBeFalse();
    });

    test('feature disabled: legacy behaviour, permissions of all roles are pooled', function () {
        PerimeterSettings::setEnabled(false);

        expect(Gate::forUser($this->alice)->allows('entity_edit', $this->e2))->toBeTrue();
    });

    test('administrators are never restricted by perimeter', function () {
        $gate = Gate::forUser($this->admin);

        expect($gate->allows('entity_edit', $this->e2))->toBeTrue()
            ->and($gate->allows('entity_delete', $this->e3))->toBeTrue();
    });
});

describe('object filtering (scopedQuery and global scope)', function () {
    test('"tous" only spans the perimeters where the user has a role', function () {
        $this->actingAs($this->alice);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expectIds(Cartographer::scopedQuery(Entity::query())->pluck('id'), [$this->e1->id, $this->e2->id]);
        expectIds(Entity::query()->pluck('id'), [$this->e1->id, $this->e2->id]);
    });

    test('a specific active perimeter narrows to that perimeter', function () {
        $this->actingAs($this->alice);

        session(['active_perimeter' => $this->p2->id]);
        expectIds(Cartographer::scopedQuery(Entity::query())->pluck('id'), [$this->e2->id]);
    });

    test('a perimeter whose role lacks <model>_access exposes nothing through scopedQuery', function () {
        $blind = roleIn($this->p3->id, 'NoEntityAccess-P3', ['application_access']);
        $bob = User::factory()->create();
        $bob->roles()->attach([$this->writerP1->id, $blind->id]);

        $this->actingAs($bob);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        // P3 is one of Bob's perimeters, but his P3 role gives no entity access there.
        expectIds(Cartographer::scopedQuery(Entity::query())->pluck('id'), [$this->e1->id]);
    });

    test('a user without any role sees nothing in "tous"', function () {
        $nobody = User::factory()->create();

        $this->actingAs($nobody);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expect(Entity::query()->count())->toBe(0);
    });

    test('administrators still see everything in "tous"', function () {
        $this->actingAs($this->admin);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expectIds(Entity::query()->pluck('id'), [$this->e1->id, $this->e2->id, $this->e3->id]);
    });

    test('feature disabled: no perimeter filtering at all', function () {
        PerimeterSettings::setEnabled(false);
        $this->actingAs($this->alice);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expect(Entity::query()->count())->toBe(3);
    });
});

describe('cartographers keep object-level delegation across perimeters', function () {
    beforeEach(function () {
        $this->carol = User::factory()->create();
        $this->carol->roles()->attach($this->writerP1);
        Cartographer::create([
            'cartographiable_type' => Entity::class,
            'cartographiable_id' => $this->e3->id,
            'user_id' => $this->carol->id,
        ]);
    });

    test('an object delegated to a cartographer is visible in "tous" whatever its perimeter', function () {
        $this->actingAs($this->carol);
        session(['active_perimeter' => Perimeter::ALL_ID]);

        expectIds(Cartographer::scopedQuery(Entity::query())->pluck('id'), [$this->e1->id, $this->e3->id]);
    });

    test('the delegation grants edit on that object only, not the role permission', function () {
        $gate = Gate::forUser($this->carol);

        expect($gate->allows('edit-object', $this->e3))->toBeTrue()
            ->and($gate->allows('entity_edit', $this->e3))->toBeFalse()
            ->and($gate->allows('edit-object', $this->e2))->toBeFalse();
    });
});

describe('a cartographer outside the object perimeter edits it without moving it', function () {
    beforeEach(function () {
        // Alice (rédactrice P1, lectrice P2, aucun rôle dans P3) est cartographe de e3 (P3).
        Cartographer::create([
            'cartographiable_type' => Entity::class,
            'cartographiable_id' => $this->e3->id,
            'user_id' => $this->alice->id,
        ]);
        $this->actingAs($this->alice);
        $this->asAll = fn () => $this->withSession(['active_perimeter' => Perimeter::ALL_ID]);
    });

    test('the edit form hides the perimeter selector, which only lists her own perimeters', function () {
        ($this->asAll)()->get(route('admin.entities.edit', $this->e3))
            ->assertOk()
            ->assertDontSee('name="perimeter_id"', false);

        ($this->asAll)()->get(route('admin.entities.edit', $this->e1))
            ->assertOk()
            ->assertSee('name="perimeter_id"', false);
    });

    test('saving the form updates the object and keeps it in its perimeter', function () {
        ($this->asAll)()
            ->put(route('admin.entities.update', $this->e3), ['name' => 'Edited by cartographer'])
            ->assertRedirect();

        expect(entityNameInDb($this->e3))->toBe('Edited by cartographer')
            ->and(entityPerimeterInDb($this->e3))->toBe($this->p3->id);
    });

    test('moving the object into one of her perimeters is refused', function () {
        $name = entityNameInDb($this->e3);

        ($this->asAll)()
            ->put(route('admin.entities.update', $this->e3), ['name' => 'Moved', 'perimeter_id' => $this->p1->id])
            ->assertForbidden();

        expect(entityNameInDb($this->e3))->toBe($name)
            ->and(entityPerimeterInDb($this->e3))->toBe($this->p3->id);
    });

    test('a cartographer holding no edit permission at all cannot move it either', function () {
        $dave = User::factory()->create();
        $dave->roles()->attach($this->readerP2);
        Cartographer::create([
            'cartographiable_type' => Entity::class,
            'cartographiable_id' => $this->e3->id,
            'user_id' => $dave->id,
        ]);

        $this->actingAs($dave)->withSession(['active_perimeter' => Perimeter::ALL_ID])
            ->put(route('admin.entities.update', $this->e3), ['name' => 'Moved', 'perimeter_id' => $this->p2->id])
            ->assertForbidden();

        expect(entityPerimeterInDb($this->e3))->toBe($this->p3->id);
    });
});

describe('HTTP writes (web)', function () {
    beforeEach(function () {
        $this->actingAs($this->alice);
        $this->asPerimeter = fn (int $id) => $this->withSession(['active_perimeter' => $id]);
    });

    test('update is refused on a read-only perimeter and left untouched', function () {
        $name = entityNameInDb($this->e2);

        ($this->asPerimeter)($this->p2->id)
            ->put(route('admin.entities.update', $this->e2), ['name' => 'HACKED', 'perimeter_id' => $this->p2->id])
            ->assertForbidden();

        expect(entityNameInDb($this->e2))->toBe($name);
    });

    test('the same user can update in the perimeter where she is a writer', function () {
        ($this->asPerimeter)($this->p1->id)
            ->put(route('admin.entities.update', $this->e1), ['name' => 'Renamed by Alice', 'perimeter_id' => $this->p1->id])
            ->assertRedirect();

        expect(entityNameInDb($this->e1))->toBe('Renamed by Alice');
    });

    test('update on the read-only perimeter is refused even with "tous" selected', function () {
        $name = entityNameInDb($this->e2);

        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->put(route('admin.entities.update', $this->e2), ['name' => 'HACKED', 'perimeter_id' => $this->p2->id])
            ->assertForbidden();

        expect(entityNameInDb($this->e2))->toBe($name);
    });

    test('delete is refused on a read-only perimeter, with a perimeter selected or with "tous"', function () {
        foreach ([$this->p2->id, Perimeter::ALL_ID] as $active) {
            ($this->asPerimeter)($active)
                ->delete(route('admin.entities.destroy', $this->e2))
                ->assertForbidden();
        }

        expect(entityDeleted($this->e2))->toBeFalse();
    });

    test('delete works in the writable perimeter', function () {
        ($this->asPerimeter)($this->p1->id)
            ->delete(route('admin.entities.destroy', $this->e1))
            ->assertRedirect();

        expect(entityDeleted($this->e1))->toBeTrue();
    });

    test('mass delete cannot reach an object of a read-only perimeter', function () {
        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->delete(route('admin.entities.massDestroy'), ['ids' => [$this->e2->id]])
            ->assertForbidden();

        expect(entityDeleted($this->e2))->toBeFalse();
    });

    test('an object cannot be moved into a perimeter where the user is read-only', function () {
        ($this->asPerimeter)($this->p1->id)
            ->put(route('admin.entities.update', $this->e1), ['name' => $this->e1->name, 'perimeter_id' => $this->p2->id])
            ->assertForbidden();

        expect(entityPerimeterInDb($this->e1))->toBe($this->p1->id);
    });

    test('an object cannot be moved into a perimeter the user has no role in', function () {
        ($this->asPerimeter)($this->p1->id)
            ->put(route('admin.entities.update', $this->e1), ['name' => $this->e1->name, 'perimeter_id' => $this->p3->id])
            ->assertForbidden();

        expect(entityPerimeterInDb($this->e1))->toBe($this->p1->id);
    });

    test('creating is refused in a read-only perimeter and allowed in a writable one', function () {
        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->post(route('admin.entities.store'), ['name' => 'Into reader perimeter', 'perimeter_id' => $this->p2->id])
            ->assertForbidden();
        expect(Entity::withoutGlobalScopes()->where('name', 'Into reader perimeter')->exists())->toBeFalse();

        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->post(route('admin.entities.store'), ['name' => 'Into writer perimeter', 'perimeter_id' => $this->p1->id])
            ->assertRedirect();
        expect(Entity::withoutGlobalScopes()->where('name', 'Into writer perimeter')->value('perimeter_id'))->toBe($this->p1->id);
    });

    test('an object created without an explicit perimeter lands in one where the user may create', function () {
        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->post(route('admin.entities.store'), ['name' => 'No perimeter given'])
            ->assertRedirect();

        expect(Entity::withoutGlobalScopes()->where('name', 'No perimeter given')->value('perimeter_id'))->toBe($this->p1->id);
    });

    test('an object of a perimeter without any role is a 403 (route binding)', function () {
        ($this->asPerimeter)(Perimeter::ALL_ID)
            ->get(route('admin.entities.show', $this->e3))
            ->assertForbidden();
    });

    test('the read-only perimeter can still be consulted', function () {
        ($this->asPerimeter)($this->p2->id)
            ->get(route('admin.entities.show', $this->e2))
            ->assertOk();
    });
});

describe('HTTP writes (API, no web session)', function () {
    beforeEach(function () {
        Passport::actingAs($this->alice);
    });

    test('the list only contains the perimeters where the user has entity_access', function () {
        $json = $this->getJson('/api/entities')->assertOk()->json();
        $ids = collect($json['data'] ?? $json)->pluck('id');

        expectIds($ids, [$this->e1->id, $this->e2->id]);
    });

    test('update is refused on a read-only perimeter and allowed on a writable one', function () {
        $this->putJson('/api/entities/'.$this->e2->id, ['name' => 'HACKED', 'perimeter_id' => $this->p2->id])
            ->assertForbidden();
        expect(entityNameInDb($this->e2))->not->toBe('HACKED');

        $this->putJson('/api/entities/'.$this->e1->id, ['name' => 'Renamed via API', 'perimeter_id' => $this->p1->id])
            ->assertSuccessful();
        expect(entityNameInDb($this->e1))->toBe('Renamed via API');
    });

    test('delete is refused on a read-only perimeter', function () {
        $this->deleteJson('/api/entities/'.$this->e2->id)->assertForbidden();

        expect(entityDeleted($this->e2))->toBeFalse();
    });

    test('mass delete cannot reach a read-only perimeter', function () {
        $this->deleteJson('/api/entities/mass-destroy', ['ids' => [$this->e2->id]])->assertForbidden();

        expect(entityDeleted($this->e2))->toBeFalse();
    });
});

describe('administrators', function () {
    test('can still write in any perimeter', function () {
        $this->actingAs($this->admin);

        $this->withSession(['active_perimeter' => Perimeter::ALL_ID])
            ->put(route('admin.entities.update', $this->e2), ['name' => 'Admin edit'])
            ->assertRedirect();

        expect(entityNameInDb($this->e2))->toBe('Admin edit');
    });
});

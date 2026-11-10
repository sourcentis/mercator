<?php

use App\Models\Application;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SavedQuery;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::forget('permissions_roles_map');

    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->withoutVite();
    $this->other = User::factory()->create();
});

function queryAccessUser(array $permissions): User
{
    $role = Role::create(['title' => 'QueryAccess'.uniqid()]);
    $role->permissions()->sync(Permission::whereIn('title', $permissions)->pluck('id'));

    $user = User::factory()->create();
    $user->roles()->attach($role);

    return $user;
}

function accessQuery(User $owner, bool $public = false): SavedQuery
{
    return SavedQuery::create([
        'name' => 'Access Query',
        'query' => ['from' => 'applications', 'fields' => ['name'], 'output' => 'list'],
        'is_public' => $public,
        'user_id' => $owner->id,
    ]);
}

describe('show', function () {
    it('refuse un utilisateur sans query_show ni query_create', function () {
        $user = queryAccessUser(['application_show']);

        $this->actingAs($user)
            ->get(route('admin.queries.show', accessQuery($user)))
            ->assertForbidden();
    });

    it('refuse la requête privée d\'un autre utilisateur', function () {
        $this->actingAs(queryAccessUser(['query_show', 'application_show']))
            ->get(route('admin.queries.show', accessQuery($this->other)))
            ->assertForbidden();
    });

    it('fournit le lien signé à qui peut lire tout le résultat', function () {
        $this->actingAs(queryAccessUser(['query_show', 'application_show']))
            ->get(route('admin.queries.show', accessQuery($this->other, public: true)))
            ->assertOk()
            ->assertViewHas('exportUrl', fn ($url) => is_string($url));
    });

    it('ne fournit pas le lien signé sans droit sur les objets de la requête', function () {
        $this->actingAs(queryAccessUser(['query_show']))
            ->get(route('admin.queries.show', accessQuery($this->other, public: true)))
            ->assertOk()
            ->assertViewHas('exportUrl', null)
            ->assertDontSee('id="btn-copy-url"', false);
    });
});

describe('massDestroy', function () {
    it('supprime uniquement les requêtes de l\'utilisateur', function () {
        $user = queryAccessUser(['query_delete']);
        $own = accessQuery($user);
        $foreign = accessQuery($this->other, public: true);

        $this->actingAs($user)
            ->deleteJson(route('admin.queries.massDestroy'), ['ids' => [$own->id, $foreign->id]])
            ->assertNoContent();

        $this->assertDatabaseMissing('saved_queries', ['id' => $own->id]);
        $this->assertDatabaseHas('saved_queries', ['id' => $foreign->id]);
    });

    it('refuse un utilisateur sans query_delete', function () {
        $user = queryAccessUser(['query_show']);
        $own = accessQuery($user);

        $this->actingAs($user)
            ->deleteJson(route('admin.queries.massDestroy'), ['ids' => [$own->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('saved_queries', ['id' => $own->id]);
    });
});

describe('API execute', function () {
    it('refuse la requête privée d\'un autre utilisateur', function () {
        Passport::actingAs(queryAccessUser(['query_show', 'application_show']));

        $this->getJson('/api/queries/execute/'.accessQuery($this->other)->id)
            ->assertForbidden();
    });

    it('refuse une requête visible sans droit sur ses objets', function () {
        Passport::actingAs(queryAccessUser(['query_show']));

        $response = $this->getJson('/api/queries/execute/'.accessQuery($this->other, public: true)->id);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('Application');
    });

    it('laisse un administrateur exécuter une requête privée', function () {
        Application::factory()->create();
        Passport::actingAs(User::query()->where('login', 'admin@admin.com')->first());

        $this->get('/api/queries/execute/'.accessQuery($this->other)->id)->assertOk();
    });

    it('exporte une requête visible avec les droits sur ses objets', function () {
        Application::factory()->create();
        Passport::actingAs(queryAccessUser(['query_show', 'application_show']));

        $response = $this->get('/api/queries/execute/'.accessQuery($this->other, public: true)->id);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });
});

describe('Interface web – écritures', function () {
    it('refuse à un propriétaire sans query_edit de modifier sa requête', function () {
        $user = queryAccessUser(['query_show']);

        $this->actingAs($user)
            ->put(route('admin.queries.update', accessQuery($user)), [
                'name' => 'Renamed',
                'query_json' => json_encode(['from' => 'applications']),
            ])
            ->assertForbidden();
    });

    it('refuse à un propriétaire sans query_delete de supprimer sa requête', function () {
        $user = queryAccessUser(['query_show']);
        $query = accessQuery($user);

        $this->actingAs($user)->delete(route('admin.queries.destroy', $query))->assertForbidden();

        $this->assertDatabaseHas('saved_queries', ['id' => $query->id]);
    });

    it('laisse le propriétaire avec query_delete supprimer sa requête', function () {
        $user = queryAccessUser(['query_delete']);
        $query = accessQuery($user);

        $this->actingAs($user)->delete(route('admin.queries.destroy', $query))->assertRedirect();

        $this->assertDatabaseMissing('saved_queries', ['id' => $query->id]);
    });

    it('masque le bouton Enregistrer sans query_edit', function () {
        $user = queryAccessUser(['query_show']);

        $this->actingAs($user)
            ->get(route('admin.queries.show', accessQuery($user)))
            ->assertOk()
            ->assertDontSee('id="btn-save"', false);
    });

    it('refuse de dupliquer la requête privée d\'un autre utilisateur', function () {
        $this->actingAs(queryAccessUser(['query_create']))
            ->post(route('admin.queries.duplicate', accessQuery($this->other)))
            ->assertForbidden();

        expect(SavedQuery::count())->toBe(1);
    });
});

describe('API – lecture et écritures', function () {
    it('ne liste pas les requêtes privées des autres utilisateurs', function () {
        $user = queryAccessUser(['query_access']);
        $own = accessQuery($user);
        $public = accessQuery($this->other, public: true);
        accessQuery($this->other);
        Passport::actingAs($user);

        $response = $this->getJson('/api/queries')->assertOk();

        $ids = collect($response->json('data') ?? $response->json())
            ->pluck('id')->sort()->values()->all();

        expect($ids)->toBe([$own->id, $public->id]);
    });

    it('refuse de lire la requête privée d\'un autre utilisateur', function () {
        Passport::actingAs(queryAccessUser(['query_show']));

        $this->getJson('/api/queries/'.accessQuery($this->other)->id)->assertForbidden();
    });

    it('refuse de modifier la requête publique d\'un autre utilisateur', function () {
        Passport::actingAs(queryAccessUser(['query_edit']));
        $query = accessQuery($this->other, public: true);

        $this->putJson('/api/queries/'.$query->id, [
            'name' => 'Hijacked',
            'query_json' => json_encode(['from' => 'applications']),
        ])->assertForbidden();

        $this->assertDatabaseHas('saved_queries', ['id' => $query->id, 'name' => 'Access Query']);
    });

    it('laisse le propriétaire modifier sa requête sans réaffecter user_id', function () {
        $user = queryAccessUser(['query_edit']);
        $query = accessQuery($user);
        Passport::actingAs($user);

        $this->putJson('/api/queries/'.$query->id, [
            'name' => 'Renamed',
            'query_json' => json_encode(['from' => 'applications']),
            'user_id' => $this->other->id,
        ])->assertOk();

        $this->assertDatabaseHas('saved_queries', ['id' => $query->id, 'name' => 'Renamed', 'user_id' => $user->id]);
    });

    it('refuse de supprimer la requête publique d\'un autre utilisateur', function () {
        Passport::actingAs(queryAccessUser(['query_delete']));
        $query = accessQuery($this->other, public: true);

        $this->deleteJson('/api/queries/'.$query->id)->assertForbidden();

        $this->assertDatabaseHas('saved_queries', ['id' => $query->id]);
    });

    it('limite mass-destroy aux requêtes de l\'utilisateur', function () {
        $user = queryAccessUser(['query_delete']);
        $own = accessQuery($user);
        $foreign = accessQuery($this->other, public: true);
        Passport::actingAs($user);

        $this->deleteJson('/api/queries/mass-destroy', ['ids' => [$own->id, $foreign->id]])->assertNoContent();

        $this->assertDatabaseMissing('saved_queries', ['id' => $own->id]);
        $this->assertDatabaseHas('saved_queries', ['id' => $foreign->id]);
    });

    it('impose l\'utilisateur courant comme propriétaire dans mass-store', function () {
        $user = queryAccessUser(['query_create']);
        Passport::actingAs($user);

        $this->putJson('/api/queries/mass-store', ['items' => [[
            'name' => 'Spoofed',
            'query' => ['from' => 'applications'],
            'user_id' => $this->other->id,
        ]]])->assertCreated();

        $this->assertDatabaseHas('saved_queries', ['name' => 'Spoofed', 'user_id' => $user->id]);
    });

    it('exige query_edit pour mass-update', function () {
        $user = queryAccessUser(['router_edit']);
        Passport::actingAs($user);

        $this->putJson('/api/queries/mass-update', ['items' => [[
            'id' => accessQuery($user)->id,
            'name' => 'Renamed',
            'query' => ['from' => 'applications'],
        ]]])->assertForbidden();
    });

    it('laisse le propriétaire utiliser mass-update sans réaffecter user_id', function () {
        $user = queryAccessUser(['query_edit']);
        $own = accessQuery($user);
        Passport::actingAs($user);

        $this->putJson('/api/queries/mass-update', ['items' => [[
            'id' => $own->id,
            'name' => 'Renamed',
            'query' => ['from' => 'applications'],
            'user_id' => $this->other->id,
        ]]])->assertOk();

        $this->assertDatabaseHas('saved_queries', ['id' => $own->id, 'name' => 'Renamed', 'user_id' => $user->id]);
    });

    it('refuse mass-update si une requête appartient à un autre utilisateur', function () {
        $user = queryAccessUser(['query_edit']);
        $own = accessQuery($user);
        $foreign = accessQuery($this->other, public: true);
        Passport::actingAs($user);

        $item = fn (SavedQuery $query) => ['id' => $query->id, 'name' => 'Renamed', 'query' => ['from' => 'applications']];

        $this->putJson('/api/queries/mass-update', ['items' => [$item($own), $item($foreign)]])->assertForbidden();

        $this->assertDatabaseHas('saved_queries', ['id' => $own->id, 'name' => 'Access Query']);
    });
});

describe('Compte en lecture seule (query_show + query_access)', function () {
    beforeEach(function () {
        $this->reader = queryAccessUser(['query_show', 'query_access', 'application_show']);
    });

    it('n\'exécute pas de DSL libre', function () {
        $this->actingAs($this->reader)
            ->postJson(route('admin.queries.execute'), ['from' => 'applications', 'fields' => ['name']])
            ->assertForbidden();
    });

    it('exécute la requête enregistrée sans tenir compte du corps envoyé', function () {
        Application::factory()->create();

        $this->actingAs($this->reader)
            ->postJson(route('admin.queries.run', accessQuery($this->reader)), ['from' => 'logical-servers'])
            ->assertOk()
            ->assertJsonPath('meta.from', 'applications')
            ->assertJsonPath('meta.count', 1);
    });

    it('n\'exécute pas la requête privée d\'un autre utilisateur', function () {
        $this->actingAs($this->reader)
            ->postJson(route('admin.queries.run', accessQuery($this->other)))
            ->assertForbidden();
    });

    it('n\'exécute pas une requête visible sans droit sur ses objets', function () {
        $this->actingAs(queryAccessUser(['query_show']))
            ->postJson(route('admin.queries.run', accessQuery($this->other, public: true)))
            ->assertUnprocessable();
    });

    it('ouvre la requête en lecture seule, sans éditeur actif', function () {
        $query = accessQuery($this->reader);

        $this->actingAs($this->reader)
            ->get(route('admin.queries.show', $query))
            ->assertOk()
            ->assertSee('readonly', false)
            ->assertSee(json_encode(route('admin.queries.run', $query)), false)
            ->assertSee('id="btn-copy-url"', false);
    });

    it('n\'affiche pas de lien sur le nom des requêtes dans la liste', function () {
        $query = accessQuery($this->reader);

        $this->actingAs($this->reader)
            ->get(route('admin.queries.index'))
            ->assertOk()
            ->assertDontSee('<a href="'.route('admin.queries.show', $query).'"', false);
    });

    it('conserve l\'éditeur et le lien pour un compte avec query_edit', function () {
        $user = queryAccessUser(['query_show', 'query_access', 'query_edit', 'application_show']);
        $query = accessQuery($user);

        $this->actingAs($user)
            ->get(route('admin.queries.index'))
            ->assertOk()
            ->assertSee('<a href="'.route('admin.queries.show', $query).'"', false);

        $this->get(route('admin.queries.show', $query))
            ->assertOk()
            ->assertSee('id="btn-save"', false)
            ->assertDontSee('readonly', false);

        $this->postJson(route('admin.queries.execute'), ['from' => 'applications', 'fields' => ['name']])
            ->assertOk();
    });
});

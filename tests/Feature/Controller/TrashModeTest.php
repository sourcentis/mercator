<?php

use App\Models\Actor;
use App\Models\Application;
use App\Models\Cartographer;
use App\Models\Document;
use App\Models\Entity;
use App\Models\Graph;
use App\Models\Process;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Support\TrashMode;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->admin);
});

function trash(object $model): void
{
    DB::table($model->getTable())->where('id', $model->id)->update(['deleted_at' => now()->subDay()]);
}

test('the cleanup tab offers to browse the trash', function () {
    $this->get(route('admin.config.parameters', ['tab' => 'cleanup']))
        ->assertOk()
        ->assertSee('id="btn-trash-enter"', false)
        ->assertSee(route('admin.trash.enter'), false);
});

test('entering the trash mode shows the overview of deleted objects', function () {
    $actor = Actor::factory()->create();
    trash($actor);
    Actor::factory()->create();

    $this->post(route('admin.trash.enter'))->assertRedirect(route('admin.trash.index'));
    expect(session(TrashMode::SESSION_KEY))->toBeTrue();

    $this->get(route('admin.trash.index'))
        ->assertOk()
        ->assertSee('trash-mode', false)
        ->assertSee('id="btn-trash-exit"', false)
        ->assertSee(route('admin.actors.index'), false)
        ->assertSeeInOrder([trans('cruds.menu.metier.title'), trans('cruds.actor.title'), '1']);
});

test('the overview groups object types by view, in the order of the menu', function () {
    foreach ([Site::class, Actor::class, Entity::class, Process::class] as $class) {
        trash($class::factory()->create());
    }

    $this->withSession([TrashMode::SESSION_KEY => true])
        ->get(route('admin.trash.index'))
        ->assertOk()
        ->assertSeeInOrder([
            trans('cruds.menu.ecosystem.title'), trans('cruds.entity.title'),
            trans('cruds.menu.metier.title'), trans('cruds.process.title'), trans('cruds.actor.title'),
            trans('cruds.menu.physical_infrastructure.title'), trans('cruds.site.title'),
        ]);
});

test('every object type with a list belongs to a view of the menu', function () {
    $inMenu = collect(TrashMode::MENU)->flatMap(fn ($types) => array_keys($types))->all();

    expect(array_diff(array_keys(TrashMode::indexRoutes()), $inMenu))->toBe([]);
});

test('leaving the trash mode restores the normal lists', function () {
    $this->withSession([TrashMode::SESSION_KEY => true])
        ->post(route('admin.trash.exit'))
        ->assertRedirect(route('admin.config.parameters', ['tab' => 'cleanup']));

    expect(session()->has(TrashMode::SESSION_KEY))->toBeFalse();

    $this->get(route('admin.actors.index'))
        ->assertOk()
        ->assertDontSee('id="btn-trash-exit"', false);
});

test('the overview redirects to the cleanup tab outside the trash mode', function () {
    $this->get(route('admin.trash.index'))
        ->assertRedirect(route('admin.config.parameters', ['tab' => 'cleanup']));
});

test('entering the trash mode requires the configure permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['auth_permissions' => ['actor_access']])
        ->post(route('admin.trash.enter'))
        ->assertForbidden();
});

function makeTrashable(string $class, User $user): object
{
    return match ($class) {
        // Pas de factory : affectation d'un cartographe à une application.
        Cartographer::class => Cartographer::create([
            'cartographiable_type' => Application::class,
            'cartographiable_id' => Application::factory()->create()->id,
            'user_id' => $user->id,
        ]),
        // La liste des graphes n'affiche que les graphes de classe 1.
        Graph::class => Graph::factory()->create(['class' => 1]),
        default => $class::factory()->create(),
    };
}

test('every list only shows deleted objects in trash mode', function (string $class) {
    $live = makeTrashable($class, $this->admin);
    $deleted = makeTrashable($class, $this->admin);
    trash($deleted);

    $route = TrashMode::indexRoutes()[$class];

    $this->withSession([TrashMode::SESSION_KEY => true])
        ->get(route($route))
        ->assertOk()
        ->assertSee('data-entry-id="'.$deleted->id.'"', false)
        ->assertDontSee('data-entry-id="'.$live->id.'"', false);

    // Hors mode corbeille (et le scope est bien rétabli après la requête précédente).
    $this->withSession([TrashMode::SESSION_KEY => false])
        ->get(route($route))
        ->assertOk()
        ->assertSee('data-entry-id="'.$live->id.'"', false)
        ->assertDontSee('data-entry-id="'.$deleted->id.'"', false);
})->with(function () {
    // Évalué avant le démarrage de l'application : scan des modèles sans façade.
    $classes = [];
    foreach (glob(__DIR__.'/../../../app/Models/*.php') as $file) {
        $class = 'App\\Models\\'.basename($file, '.php');
        if (in_array($class, [User::class, Role::class, Document::class], true)) {
            continue;
        }
        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $classes[class_basename($class)] = [$class];
        }
    }

    return $classes;
});

test('a deleted object can be displayed only in trash mode', function () {
    $actor = Actor::factory()->create();
    trash($actor);

    $this->get(route('admin.actors.show', $actor->id))->assertNotFound();

    $this->withSession([TrashMode::SESSION_KEY => true])
        ->get(route('admin.actors.show', $actor->id))
        ->assertOk()
        ->assertSee('id="trash-object"', false)
        ->assertSee(route('admin.trash.restore', ['actor', $actor->id]), false);
});

test('a deleted object is restored', function () {
    $application = Application::factory()->create();
    trash($application);

    $this->withSession([TrashMode::SESSION_KEY => true])
        ->post(route('admin.trash.restore', ['application', $application->id]))
        ->assertRedirect()
        ->assertSessionHas('message');

    expect(Application::find($application->id))->not->toBeNull()
        ->and(Application::find($application->id)->deleted_by)->toBeNull();
});

test('restoring requires the delete permission of the object type', function () {
    $actor = Actor::factory()->create();
    trash($actor);

    $user = User::factory()->create();
    $this->actingAs($user)
        ->withSession([TrashMode::SESSION_KEY => true, 'auth_permissions' => ['actor_access', 'actor_show']])
        ->post(route('admin.trash.restore', ['actor', $actor->id]))
        ->assertForbidden();

    expect(Actor::find($actor->id))->toBeNull();
});

test('an object is not restored when an active object already has its name', function () {
    $deleted = Actor::factory()->create(['name' => 'Duplicate'])->refresh();
    trash($deleted);
    Actor::factory()->create(['name' => 'Duplicate', 'perimeter_id' => $deleted->perimeter_id]);

    $this->withSession([TrashMode::SESSION_KEY => true])
        ->post(route('admin.trash.restore', ['actor', $deleted->id]))
        ->assertSessionHasErrors();

    expect(Actor::find($deleted->id))->toBeNull();
});

test('an unknown object type cannot be restored', function () {
    $this->withSession([TrashMode::SESSION_KEY => true])
        ->post(route('admin.trash.restore', ['user', $this->admin->id]))
        ->assertNotFound();
});

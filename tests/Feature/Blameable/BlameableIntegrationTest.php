<?php

use App\Events\CartographerModifiedObject;
use App\Models\Application;
use App\Models\Building;
use App\Models\Cartographer;
use App\Models\Subnetwork;
use App\Models\User;
use App\Models\Vlan;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);
    $this->admin = User::query()->where('login', 'admin@admin.com')->firstOrFail();
});

it('blames the token user when creating through the API', function () {
    Artisan::call('passport:client', ['--personal' => true, '--name' => 'pat', '--provider' => 'users', '--no-interaction' => true]);

    $token = $this->postJson('/api/login', ['login' => 'admin@admin.com', 'password' => 'password'])
        ->assertOk()
        ->json('access_token');

    // Drop the session/guard state left by the login call: only the bearer token authenticates
    $this->app['auth']->forgetGuards();

    $id = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/applications', ['name' => 'Created through API'])
        ->assertCreated()
        ->json('id');

    $row = DB::table('applications')->where('id', $id)->first();
    expect($row->created_by)->toBe($this->admin->id)
        ->and($row->updated_by)->toBe($this->admin->id);
});

it('does not report updated_by as a cartographer modification', function () {
    Config::set('mercator.cartography.notifier_enabled', true);
    Event::fake([CartographerModifiedObject::class]);

    $user = User::factory()->create();
    $application = Application::factory()->create();
    Cartographer::create([
        'cartographiable_type' => Application::class,
        'cartographiable_id' => $application->id,
        'user_id' => $user->id,
    ]);
    session([
        'cartographer_permissions' => [Application::class => [$application->id]],
        'cartographer_permissions_at' => now()->timestamp,
    ]);
    $this->actingAs($user);

    $application->update(['name' => 'Modified by cartographer']);

    expect(DB::table('applications')->where('id', $application->id)->value('updated_by'))->toBe($user->id);
    Event::assertDispatched(CartographerModifiedObject::class, function (CartographerModifiedObject $event) {
        return array_key_exists('name', $event->dirty)
            && ! array_key_exists('updated_by', $event->dirty);
    });
});

it('sets updated_by on buildings attached in bulk', function () {
    $this->actingAs($this->admin);
    $building = Building::factory()->create();
    $children = Building::factory()->count(2)->create();
    DB::table('buildings')->whereIn('id', $children->pluck('id'))->update(['updated_by' => null]);

    $other = User::factory()->create();
    $this->admin->roles->each(fn ($role) => $other->roles()->attach($role->id));
    $this->actingAs($other);

    $this->put(route('admin.buildings.update', $building), [
        'name' => $building->name,
        'buildings' => $children->pluck('id')->all(),
    ])->assertRedirect(route('admin.buildings.index'));

    foreach ($children as $child) {
        $row = DB::table('buildings')->where('id', $child->id)->first();
        expect($row->building_id)->toBe($building->id)
            ->and($row->updated_by)->toBe($other->id);
    }
});

it('sets updated_by on subnetworks attached to a vlan', function () {
    $this->actingAs($this->admin);
    $vlan = Vlan::factory()->create();
    $subnetwork = Subnetwork::factory()->create();
    DB::table('subnetworks')->where('id', $subnetwork->id)->update(['updated_by' => null]);

    $this->put(route('admin.vlans.update', $vlan), [
        'name' => $vlan->name,
        'subnetworks' => [$subnetwork->id],
    ])->assertRedirect();

    $row = DB::table('subnetworks')->where('id', $subnetwork->id)->first();
    expect($row->vlan_id)->toBe($vlan->id)
        ->and($row->updated_by)->toBe($this->admin->id);
});

it('records deleted_by on mass destroy through the API', function () {
    $applications = Application::factory()->count(2)->create();

    Passport::actingAs($this->admin);

    $this->deleteJson('/api/applications/mass-destroy', ['ids' => $applications->pluck('id')->all()])
        ->assertNoContent();

    foreach ($applications as $application) {
        $row = DB::table('applications')->where('id', $application->id)->first();
        expect($row->deleted_at)->not->toBeNull()
            ->and($row->deleted_by)->toBe($this->admin->id);
    }
    expect(DB::table('audit_logs')->where('subject_type', Application::class)
        ->where('description', 'deleted')->count())->toBe(2);
});

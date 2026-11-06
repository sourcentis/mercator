<?php

use App\Models\Application;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    $this->creator = User::factory()->create(['name' => 'Alice Creator']);
    $this->updater = User::factory()->create(['name' => 'Bob Updater']);

    $this->actingAs($this->creator);
    $this->application = Application::factory()->create();
    $this->actingAs($this->updater);
    $this->application->update(['name' => 'Updated '.uniqid()]);
});

function footerReader(bool $withAuditLog): User
{
    $titles = ['application_access', 'application_show'];
    if ($withAuditLog) {
        $titles[] = 'audit_log_access';
    }
    $role = Role::factory()->create();
    $role->permissions()->attach(Permission::query()->whereIn('title', $titles)->pluck('id'));
    $user = User::factory()->create();
    $user->roles()->attach($role->id);

    return $user;
}

it('shows the creator and last editor to users with audit_log_access', function () {
    $this->actingAs(footerReader(true))
        ->get(route('admin.applications.show', $this->application))
        ->assertOk()
        ->assertSee(trans('global.by').' Alice Creator')
        ->assertSee(trans('global.by').' Bob Updater')
        ->assertDontSee(trans('global.deleted_user'));
});

it('shows the dates but not the names without audit_log_access', function () {
    $this->actingAs(footerReader(false))
        ->get(route('admin.applications.show', $this->application))
        ->assertOk()
        ->assertSee(trans('global.created_at'))
        ->assertSee(trans('global.updated_at'))
        ->assertDontSee('Alice Creator')
        ->assertDontSee('Bob Updater');
});

it('flags a soft-deleted author', function () {
    $this->creator->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.applications.show', $this->application))
        ->assertOk()
        ->assertSee('Alice Creator ('.trans('global.deleted_user').')', false);
});

it('escapes the author name', function () {
    $this->updater->forceFill(['name' => '<script>alert(1)</script>'])->save();

    $this->actingAs($this->admin)
        ->get(route('admin.applications.show', $this->application))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

<?php

use App\Models\Parameter;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());
});

function storedCleanup(): ?array
{
    $stored = Parameter::getValue('mercator_config');

    return $stored === null ? null : (json_decode($stored, true)['cleanup'] ?? null);
}

test('cleanup tab is displayed with default values', function () {
    $this->get(route('admin.config.parameters', ['tab' => 'cleanup']))
        ->assertOk()
        ->assertViewHas('cleanup_audit_logs_enabled', false)
        ->assertViewHas('cleanup_audit_logs_retention', 12)
        ->assertViewHas('cleanup_deleted_objects_enabled', false)
        ->assertViewHas('cleanup_deleted_objects_retention', 3)
        ->assertSee('id="tab-cleanup"', false)
        ->assertSee('name="audit_logs_retention"', false)
        ->assertSee('name="deleted_objects_retention"', false);
});

test('cleanup settings are saved', function () {
    $this->put(route('admin.config.parameters'), [
        'active_tab' => 'cleanup',
        'audit_logs_enabled' => 'on',
        'audit_logs_retention' => 24,
        'deleted_objects_enabled' => 'on',
        'deleted_objects_retention' => 6,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(storedCleanup())->toBe([
        'audit_logs_enabled' => true,
        'audit_logs_retention' => 24,
        'deleted_objects_enabled' => true,
        'deleted_objects_retention' => 6,
    ]);
    expect(config('mercator.cleanup.audit_logs_retention'))->toBe(24);
});

test('a disabled purge keeps its previous retention period', function () {
    $this->put(route('admin.config.parameters'), [
        'active_tab' => 'cleanup',
        'audit_logs_enabled' => 'on',
        'audit_logs_retention' => 36,
        'deleted_objects_enabled' => 'on',
        'deleted_objects_retention' => 6,
    ]);

    // Fieldset désactivé : le select n'est pas transmis.
    $this->put(route('admin.config.parameters'), [
        'active_tab' => 'cleanup',
        'deleted_objects_enabled' => 'on',
        'deleted_objects_retention' => 6,
    ])->assertSessionHasNoErrors();

    expect(storedCleanup()['audit_logs_enabled'])->toBeFalse()
        ->and(storedCleanup()['audit_logs_retention'])->toBe(36);
});

test('a retention period outside the proposed values is rejected', function () {
    $this->put(route('admin.config.parameters'), [
        'active_tab' => 'cleanup',
        'audit_logs_enabled' => 'on',
        'audit_logs_retention' => 7,
    ])->assertSessionHasErrors('audit_logs_retention');

    expect(storedCleanup())->toBeNull();
});

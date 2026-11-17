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

    $this->admin = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->admin);
});

function storedMaxNodes(): ?int
{
    $stored = Parameter::getValue('mercator_config');

    return $stored === null ? null : (json_decode($stored, true)['parameters']['max_nodes'] ?? null);
}

describe('max_nodes parameter', function () {
    test('general tab shows the max_nodes field with its default value', function () {
        $response = $this->get(route('admin.config.parameters'));

        $response->assertOk();
        $response->assertViewHas('max_nodes', 500);
        $response->assertSee('name="max_nodes"', false);
        $response->assertSee('value="500"', false);
    });

    test('a valid value is persisted and read back', function () {
        $response = $this->put(route('admin.config.parameters'), [
            'active_tab' => 'general',
            'max_nodes' => 250,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        expect(storedMaxNodes())->toBe(250);
        expect(config('mercator.parameters.max_nodes'))->toBe(250);

        $this->get(route('admin.config.parameters'))
            ->assertOk()
            ->assertViewHas('max_nodes', 250);
    });

    test('zero (no limit) is accepted', function () {
        $this->put(route('admin.config.parameters'), [
            'active_tab' => 'general',
            'max_nodes' => 0,
        ])->assertSessionHasNoErrors();

        expect(storedMaxNodes())->toBe(0);
    });

    test('invalid values are rejected and the stored value is unchanged', function (mixed $value) {
        $this->put(route('admin.config.parameters'), [
            'active_tab' => 'general',
            'max_nodes' => 250,
        ])->assertSessionHasNoErrors();

        $response = $this->from(route('admin.config.parameters'))
            ->put(route('admin.config.parameters'), [
                'active_tab' => 'general',
                'max_nodes' => $value,
            ]);

        $response->assertRedirect(route('admin.config.parameters'));
        $response->assertSessionHasErrors('max_nodes');

        expect(storedMaxNodes())->toBe(250);
    })->with([-1, 'abc', 100001]);

    test('denies access without the configure permission', function () {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.config.parameters'))->assertForbidden();

        $this->put(route('admin.config.parameters'), [
            'active_tab' => 'general',
            'max_nodes' => 10,
        ])->assertForbidden();

        expect(storedMaxNodes())->toBeNull();
    });
});

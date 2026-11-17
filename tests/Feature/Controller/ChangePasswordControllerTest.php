<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    // La règle uncompromised() interroge l'API Have I Been Pwned
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

    $this->newPassword = 'N3w-Str0ng!Passw0rd';

});

function passwordPayload(User $user, string $password): array
{
    return [
        'email' => $user->email,
        'password' => $password,
        'password_confirmation' => $password,
    ];
}

describe('edit', function () {

    test('displays the change password form', function () {
        $this->actingAs(User::query()->where('login', 'admin@admin.com')->first());

        $this->get(route('profile.password.edit'))
            ->assertOk()
            ->assertViewIs('auth.passwords.edit');
    });

    test('denies access without permission', function () {
        $this->actingAs(User::factory()->create());

        $this->get(route('profile.password.edit'))->assertForbidden();
    });

});

describe('update', function () {

    test('admin can change his password', function () {
        $admin = User::query()->where('login', 'admin@admin.com')->first();
        $this->actingAs($admin);

        $this->post(route('profile.password.update'), passwordPayload($admin, $this->newPassword))
            ->assertRedirect(route('profile.password.edit'))
            ->assertSessionHasNoErrors();

        expect(Hash::check($this->newPassword, $admin->fresh()->password))->toBeTrue();
    });

    // #2774 : un utilisateur non admin ayant profile_password_edit recevait un 403
    test('non-admin user with permission can change his password', function () {
        $user = User::factory()->create();
        $user->roles()->sync([Role::query()->where('title', 'User')->firstOrFail()->id]);
        $this->actingAs($user);

        $this->post(route('profile.password.update'), passwordPayload($user, $this->newPassword))
            ->assertRedirect(route('profile.password.edit'))
            ->assertSessionHasNoErrors();

        expect(Hash::check($this->newPassword, $user->fresh()->password))->toBeTrue();
    });

    test('denies update without permission', function () {
        $user = User::factory()->create();
        $oldHash = $user->password;
        $this->actingAs($user);

        $this->post(route('profile.password.update'), passwordPayload($user, $this->newPassword))
            ->assertForbidden();

        expect($user->fresh()->password)->toBe($oldHash);
    });

});

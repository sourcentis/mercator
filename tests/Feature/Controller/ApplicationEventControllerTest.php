<?php

use App\Models\Application;
use App\Models\ApplicationEvent;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        PermissionsTableSeeder::class,
        RolesTableSeeder::class,
        PermissionRoleTableSeeder::class,
        UsersTableSeeder::class,
        RoleUserTableSeeder::class,
    ]);

    $this->user = User::query()->where('login', 'admin@admin.com')->first();
    $this->actingAs($this->user);
});

describe('ApplicationEventController', function () {

    test('index returns events for given application id', function () {
        $app = Application::factory()->create();

        // Create a couple of events attached to the application
        $e1 = ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->user)
            ->create(['message' => 'First event']);

        $e2 = ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->user)
            ->create(['message' => 'Second event']);

        $response = $this->get(route('admin.application-events.index', ['id' => $app->id]));

        $response->assertOk();
        $response->assertJsonFragment(['message' => 'First event']);
        $response->assertJsonFragment(['message' => 'Second event']);
        $this->assertCount(2, $response->json());
    });

    test('store creates a new event and returns updated events list', function () {
        $app = Application::factory()->create();

        $payload = [
            'application_id' => $app->id,
            'message' => 'New event message',
        ];

        $response = $this->post(route('admin.application-events.store'), $payload);

        $response->assertCreated();  // 201
        $response->assertJsonStructure(['events']);
        $response->assertJsonFragment(['message' => 'New event message']);

        $this->assertDatabaseHas('application_events', [
            'application_id' => $app->id,
            'user_id' => $this->user->id,
            'message' => 'New event message',
        ]);
    });

    test('destroy removes an event and returns remaining list', function () {
        $app = Application::factory()->create();
        $eventToDelete = ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->user)
            ->create(['message' => 'To be deleted']);

        $eventToKeep = ApplicationEvent::factory()
            ->for($app, 'application')
            ->for($this->user)
            ->create(['message' => 'To keep']);

        $response = $this->delete(
            route('admin.application-events.destroy', $eventToDelete->id),
            ['application_id' => $app->id]
        );

        $response->assertRedirectBack();

        $this->assertDatabaseMissing('application_events', [
            'id' => $eventToDelete->id,
        ]);

        $this->assertDatabaseHas('application_events', [
            'id' => $eventToKeep->id,
        ]);
    });
});

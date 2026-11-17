<?php

use App\Models\Entity;
use App\Models\User;
use App\Services\Graph\GraphSize;
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

describe('GraphSize', function () {
    test('count sums the node collections', function () {
        expect(GraphSize::count(collect([1, 2]), [3], collect()))->toBe(3);
    });

    test('tooLarge honours the configured limit, 0 meaning no limit', function () {
        config(['mercator.parameters.max_nodes' => 5]);
        expect(GraphSize::tooLarge(5))->toBeNull();
        expect(GraphSize::tooLarge(6))->toBe(['count' => 6, 'max' => 5]);

        config(['mercator.parameters.max_nodes' => 0]);
        expect(GraphSize::tooLarge(100000))->toBeNull();
    });
});

describe('report views', function () {
    test('an oversized graph is not built and the message is rendered server-side', function () {
        config(['mercator.parameters.max_nodes' => 2]);
        Entity::factory()->count(3)->create();

        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertOk();
        $response->assertViewHas('graphTooLarge', ['count' => 3, 'max' => 2]);
        $response->assertViewHas('dotSrc', '');
        $response->assertSee('data-graph-too-large', false);
        $response->assertSee(trans('global.graph_too_large', ['count' => 3, 'max' => 2]));
        // Objects are not listed below the graph either
        $response->assertDontSee('report-scroll-area', false);
    });

    test('a graph within the limit is built normally', function () {
        config(['mercator.parameters.max_nodes' => 3]);
        $entity = Entity::factory()->create();

        $response = $this->get(route('admin.report.view.ecosystem'));

        $response->assertOk();
        $response->assertViewHas('graphTooLarge', null);
        expect($response->viewData('dotSrc'))->toContain('E'.$entity->id);
        $response->assertDontSee('data-graph-too-large', false);
        $response->assertSee('report-scroll-area', false);
    });

    test('every report view exposes graphTooLarge', function (string $route) {
        config(['mercator.parameters.max_nodes' => 0]);

        $response = $this->get(route($route));

        $response->assertOk();
        $response->assertViewHas('graphTooLarge', null);
        $response->assertSee('report-scroll-area', false);
    })->with([
        'admin.report.view.ecosystem',
        'admin.report.view.information-system',
        'admin.report.view.applications',
        'admin.report.view.application-flows',
        'admin.report.view.logical-infrastructure',
        'admin.report.view.administration',
        'admin.report.view.physical-infrastructure',
        'admin.report.view.network-infrastructure',
        'admin.report.view.security-zones',
        'admin.report.gdpr',
    ]);
});

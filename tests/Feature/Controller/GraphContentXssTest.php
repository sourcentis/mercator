<?php

use App\Models\Graph;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

// GHSA-cr24-j2fr-r2gg : graph content was emitted raw inside a JS template literal
uses(RefreshDatabase::class);

// Well-formed XML (the BPMN show page parses it) carrying a backtick, a ${...} and a </script>
const GRAPH_XSS_PAYLOAD = '<definitions id="`;alert(1)//${alert(2)}"><script>alert(3)</script></definitions>';

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

function assertGraphContentEscaped($response): void
{
    $response->assertOk();
    $response->assertDontSee(GRAPH_XSS_PAYLOAD, false);
    $response->assertDontSee('alert(3)</script>', false);
}

test('map show page does not render graph content raw', function () {
    $graph = Graph::factory()->create(['class' => 1, 'content' => GRAPH_XSS_PAYLOAD]);

    $response = $this->get(route('admin.graphs.show', $graph));

    assertGraphContentEscaped($response);
    $response->assertSee('const xmlContent = '.json_encode(GRAPH_XSS_PAYLOAD, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT).';', false);
});

test('map index page does not render graph content raw', function () {
    Graph::factory()->create(['class' => 1, 'content' => GRAPH_XSS_PAYLOAD]);

    assertGraphContentEscaped($this->get(route('admin.graphs.index')));
});

test('bpmn show page does not render graph content raw', function () {
    $graph = Graph::factory()->create(['class' => 2, 'content' => GRAPH_XSS_PAYLOAD]);

    assertGraphContentEscaped($this->get(route('admin.bpmn.show', $graph->id)));
});

test('bpmn edit page does not render graph content raw', function () {
    $graph = Graph::factory()->create(['class' => 2, 'content' => GRAPH_XSS_PAYLOAD]);

    assertGraphContentEscaped($this->get(route('admin.bpmn.edit', $graph->id)));
});

test('bpmn raw page does not render graph content raw', function () {
    $graph = Graph::factory()->create(['class' => 2, 'content' => GRAPH_XSS_PAYLOAD]);

    assertGraphContentEscaped($this->get(route('admin.bpmn.raw', $graph->id)));
});

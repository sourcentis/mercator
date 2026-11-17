<?php

use App\Models\Graph;
use App\Models\User;
use Database\Seeders\PermissionRoleTableSeeder;
use Database\Seeders\PermissionsTableSeeder;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\RoleUserTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

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
    Passport::actingAs($this->user);
});

const GRAPH_XML = '<mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/>'
    .'<mxCell id="2" value="&lt;b&gt;App&lt;/b&gt;" style="#APP_1" vertex="1" parent="1"/></root></mxGraphModel>';

// ============================================================
// index
// ============================================================

it('forbids listing graphs without permission', function () {
    Passport::actingAs(User::factory()->create());

    $this->getJson('/api/graphs')->assertForbidden();
});

it('lists graphs when permitted', function () {
    Graph::factory()->count(3)->create();

    expect($this->getJson('/api/graphs')->assertOk()->json())->toHaveCount(3);
});

it('filters graphs by class', function () {
    Graph::factory()->create(['name' => 'Graph Map', 'class' => 1]);
    Graph::factory()->create(['name' => 'Graph BPMN', 'class' => 2]);

    $data = $this->getJson('/api/graphs?filter[class]=2')->assertOk()->json();

    expect($data)->toHaveCount(1)
        ->and($data[0]['name'])->toBe('Graph BPMN');
});

// ============================================================
// store
// ============================================================

it('forbids creating a graph without permission', function () {
    Passport::actingAs(User::factory()->create());

    $this->postJson('/api/graphs', ['name' => 'Graph Test'])->assertForbidden();
});

it('creates a graph and keeps its XML content intact', function () {
    $this->postJson('/api/graphs', [
        'name' => 'Graph Test',
        'class' => 1,
        'type' => 'logical',
        'content' => GRAPH_XML,
    ])
        ->assertCreated()
        ->assertJsonFragment(['name' => 'Graph Test']);

    expect(Graph::where('name', 'Graph Test')->first()->content)->toBe(GRAPH_XML);
});

it('rejects creating a graph without name', function () {
    $this->postJson('/api/graphs', ['type' => 'logical'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('allows duplicate graph names', function () {
    Graph::factory()->create(['name' => 'Same Name']);

    $this->postJson('/api/graphs', ['name' => 'Same Name'])->assertCreated();
});

// ============================================================
// show
// ============================================================

it('forbids showing a graph without permission', function () {
    Passport::actingAs(User::factory()->create());

    $graph = Graph::factory()->create();

    $this->getJson("/api/graphs/{$graph->id}")->assertForbidden();
});

it('shows a graph when permitted', function () {
    $graph = Graph::factory()->create(['name' => 'Graph Visible', 'content' => GRAPH_XML]);

    $this->getJson("/api/graphs/{$graph->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Graph Visible')
        ->assertJsonPath('data.content', GRAPH_XML);
});

// ============================================================
// update
// ============================================================

it('forbids updating a graph without permission', function () {
    Passport::actingAs(User::factory()->create());

    $graph = Graph::factory()->create();

    $this->putJson("/api/graphs/{$graph->id}", ['name' => 'Updated'])->assertForbidden();
});

it('updates a graph', function () {
    $graph = Graph::factory()->create(['name' => 'Old Name']);

    $this->putJson("/api/graphs/{$graph->id}", [
        'name' => 'New Name',
        'content' => GRAPH_XML,
    ])->assertOk();

    $graph->refresh();
    expect($graph->name)->toBe('New Name')
        ->and($graph->content)->toBe(GRAPH_XML);
});

// ============================================================
// destroy
// ============================================================

it('forbids deleting a graph without permission', function () {
    Passport::actingAs(User::factory()->create());

    $graph = Graph::factory()->create();

    $this->deleteJson("/api/graphs/{$graph->id}")->assertForbidden();
});

it('soft deletes a graph', function () {
    $graph = Graph::factory()->create();

    $this->deleteJson("/api/graphs/{$graph->id}")->assertOk();

    $this->assertSoftDeleted('graphs', ['id' => $graph->id]);
});

// ============================================================
// mass operations
// ============================================================

it('mass stores graphs', function () {
    $this->postJson('/api/graphs/mass-store', [
        'items' => [
            ['name' => 'Graph A', 'content' => GRAPH_XML],
            ['name' => 'Graph B', 'class' => 2],
        ],
    ])
        ->assertCreated()
        ->assertJsonPath('count', 2);

    expect(Graph::where('name', 'Graph A')->first()->content)->toBe(GRAPH_XML);
    $this->assertDatabaseHas('graphs', ['name' => 'Graph B', 'class' => 2]);
});

it('mass updates graphs', function () {
    $graphs = Graph::factory()->count(2)->create();

    $this->putJson('/api/graphs/mass-update', [
        'items' => [
            ['id' => $graphs[0]->id, 'name' => 'Renamed A', 'content' => GRAPH_XML],
            ['id' => $graphs[1]->id, 'name' => 'Renamed B'],
        ],
    ])->assertOk();

    expect($graphs[0]->fresh()->content)->toBe(GRAPH_XML);
    $this->assertDatabaseHas('graphs', ['id' => $graphs[1]->id, 'name' => 'Renamed B']);
});

it('mass destroys graphs', function () {
    $graphs = Graph::factory()->count(2)->create();

    $this->deleteJson('/api/graphs/mass-destroy', ['ids' => $graphs->pluck('id')->all()])
        ->assertNoContent();

    foreach ($graphs as $graph) {
        $this->assertSoftDeleted('graphs', ['id' => $graph->id]);
    }
});

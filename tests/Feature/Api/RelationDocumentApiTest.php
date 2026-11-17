<?php

use App\Models\Document;
use App\Models\Entity;
use App\Models\Relation;
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

it('exposes document ids when showing a relation', function () {
    $relation = Relation::factory()->create();
    $documents = Document::factory()->count(2)->create();
    $relation->documents()->sync($documents->pluck('id'));

    $this->getJson("/api/relations/{$relation->id}")
        ->assertOk()
        ->assertJsonPath('data.documents', $documents->pluck('id')->all());
});

it('includes documents in the relation list', function () {
    $relation = Relation::factory()->create();
    $document = Document::factory()->create();
    $relation->documents()->sync([$document->id]);

    $data = $this->getJson('/api/relations?include=documents')->assertOk()->json();
    $data = $data['data'] ?? $data;

    expect($data[0]['documents'][0]['id'])->toBe($document->id);
});

it('syncs documents on store and update', function () {
    $source = Entity::factory()->create();
    $destination = Entity::factory()->create();
    [$doc1, $doc2] = Document::factory()->count(2)->create();

    $id = $this->postJson('/api/relations', [
        'name' => 'Contract',
        'source_id' => $source->id,
        'destination_id' => $destination->id,
        'documents' => [$doc1->id],
    ])->assertCreated()->json('id');

    expect(Relation::find($id)->documents->pluck('id')->all())->toBe([$doc1->id]);

    $this->putJson("/api/relations/{$id}", [
        'name' => 'Contract',
        'source_id' => $source->id,
        'destination_id' => $destination->id,
        'documents' => [$doc2->id],
    ])->assertOk();

    expect(Relation::find($id)->documents->pluck('id')->all())->toBe([$doc2->id]);
});

it('rejects unknown document ids', function () {
    $source = Entity::factory()->create();
    $destination = Entity::factory()->create();

    $this->postJson('/api/relations', [
        'name' => 'Contract',
        'source_id' => $source->id,
        'destination_id' => $destination->id,
        'documents' => [999999],
    ])->assertUnprocessable();
});

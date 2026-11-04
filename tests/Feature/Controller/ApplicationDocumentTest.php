<?php

use App\Models\Application;
use App\Models\Document;
use App\Models\Parameter;
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

describe('pivot table and relation', function () {
    test('application_document table exists and documents() relation works', function () {
        $application = Application::factory()->create();
        $document = Document::factory()->create();

        $application->documents()->attach($document->id);

        $application->refresh();
        expect($application->documents)->toHaveCount(1)
            ->and($application->documents->first()->id)->toBe($document->id);
    });
});

describe('store with documents', function () {
    test('store associates session documents with the new application', function () {
        $document = Document::factory()->create();

        $this->withSession(['documents' => [$document->id]]);

        $response = $this->post(route('admin.applications.store'), [
            'name' => 'Test Application',
        ]);

        $response->assertRedirect(route('admin.applications.index'));

        $application = Application::query()->where('name', 'Test Application')->firstOrFail();
        expect($application->documents)->toHaveCount(1)
            ->and($application->documents->first()->id)->toBe($document->id);
    });
});

describe('update with documents', function () {
    test('update synchronises the document list', function () {
        $application = Application::factory()->create();
        $doc1 = Document::factory()->create();
        $doc2 = Document::factory()->create();

        $application->documents()->attach($doc1->id);

        $this->withSession(['documents' => [$doc2->id]]);

        $response = $this->put(route('admin.applications.update', $application), [
            'name' => $application->name,
        ]);

        $response->assertRedirect(route('admin.applications.index'));

        $application->refresh();
        expect($application->documents)->toHaveCount(1)
            ->and($application->documents->first()->id)->toBe($doc2->id);
    });
});

describe('show view documents section', function () {
    test('displays documents section when parameter is active', function () {
        config(['mercator.parameters.application_documents' => true]);

        $application = Application::factory()->create();
        $document = Document::factory()->create(['filename' => 'test-doc.pdf']);
        $application->documents()->attach($document->id);

        $response = $this->get(route('admin.applications.show', $application->id));

        $response->assertOk();
        $response->assertSee('test-doc.pdf');
    });

    test('hides documents section when parameter is inactive', function () {
        config(['mercator.parameters.application_documents' => false]);

        $application = Application::factory()->create();
        $document = Document::factory()->create(['filename' => 'hidden-doc.pdf']);
        $application->documents()->attach($document->id);

        $response = $this->get(route('admin.applications.show', $application->id));

        $response->assertOk();
        $response->assertDontSee('hidden-doc.pdf');
    });
});

describe('configuration parameter save', function () {
    test('handleGeneral saves application_documents parameter', function () {
        $response = $this->put(route('admin.config.parameters'), [
            'active_tab' => 'general',
            'application_documents' => '1',
            'max_nodes' => 500,
        ]);

        $response->assertRedirect();

        expect(config('mercator.parameters.application_documents'))->toBeTrue();

        $stored = json_decode(Parameter::getValue('mercator_config'), true);
        expect($stored['parameters']['application_documents'])->toBeTrue();
    });
});

<?php

use App\Jobs\ApplicationDeploymentJob;
use App\Models\EnvironmentVariable;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads only non-preview server environment variables through relation', function () {
    $user = User::factory()->create();
    $team = $user->teams()->first();
    $server = Server::factory()->create(['team_id' => $team->id]);

    EnvironmentVariable::create([
        'key' => 'SERVER_REGION',
        'value' => 'eu-central-1',
        'is_preview' => false,
        'is_runtime' => true,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    EnvironmentVariable::create([
        'key' => 'SERVER_REGION_PREVIEW',
        'value' => 'preview-only',
        'is_preview' => true,
        'is_runtime' => true,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    $keys = $server->environment_variables()->pluck('key')->all();

    expect($keys)->toContain('SERVER_REGION')
        ->and($keys)->not->toContain('SERVER_REGION_PREVIEW');
});

it('filters runtime server environment variables for deployments', function () {
    $user = User::factory()->create();
    $team = $user->teams()->first();
    $server = Server::factory()->create(['team_id' => $team->id]);

    EnvironmentVariable::create([
        'key' => 'ZZ_SERVER_NODE',
        'value' => 'node-b',
        'is_preview' => false,
        'is_runtime' => true,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    EnvironmentVariable::create([
        'key' => 'AA_SERVER_NODE',
        'value' => 'node-a',
        'is_preview' => false,
        'is_runtime' => true,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    EnvironmentVariable::create([
        'key' => 'SERVER_BUILD_ONLY',
        'value' => 'ignored',
        'is_preview' => false,
        'is_runtime' => false,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    EnvironmentVariable::create([
        'key' => 'NIXPACKS_INSTALL_CMD',
        'value' => 'ignored',
        'is_preview' => false,
        'is_runtime' => true,
        'is_buildtime' => true,
        'resourceable_type' => Server::class,
        'resourceable_id' => $server->id,
    ]);

    $reflection = new ReflectionClass(ApplicationDeploymentJob::class);
    $job = $reflection->newInstanceWithoutConstructor();

    $serverProperty = $reflection->getProperty('server');
    $serverProperty->setAccessible(true);
    $serverProperty->setValue($job, $server);

    $method = $reflection->getMethod('getServerRuntimeEnvironmentVariables');
    $method->setAccessible(true);

    $result = $method->invoke($job);
    $keys = $result->pluck('key')->values()->all();

    expect($keys)->toBe(['AA_SERVER_NODE', 'ZZ_SERVER_NODE']);
});

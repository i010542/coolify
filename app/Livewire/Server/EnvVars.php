<?php

namespace App\Livewire\Server;

use App\Models\Server;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EnvVars extends Component
{
    public Server $server;

    public array $parameters = [];

    public array $envVars = [];

    public string $newKey = '';

    public string $newValue = '';

    public function mount(string $server_uuid): void
    {
        try {
            $this->server = Server::ownedByCurrentTeam()->whereUuid($server_uuid)->firstOrFail();
            $this->parameters = get_route_parameters();
            $this->envVars = $this->server->predefined_env_vars ?? [];
        } catch (\Throwable) {
            redirect()->route('server.index');
        }
    }

    public function addEnvVar(): void
    {
        $this->authorize('update', $this->server);

        $key = trim($this->newKey);
        $value = $this->newValue;

        if (blank($key)) {
            $this->dispatch('error', 'Key cannot be empty.');

            return;
        }

        // Validate key format (letters, digits, underscores, cannot start with digit)
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            $this->dispatch('error', 'Invalid key format. Use letters, digits, and underscores only.');

            return;
        }

        $existing = collect($this->envVars)->pluck('key')->toArray();
        if (in_array($key, $existing)) {
            $this->dispatch('error', "Key '{$key}' already exists.");

            return;
        }

        $this->envVars[] = ['key' => $key, 'value' => $value];
        $this->save();

        $this->newKey = '';
        $this->newValue = '';
        $this->dispatch('success', 'Environment variable added.');
    }

    public function removeEnvVar(int $index): void
    {
        $this->authorize('update', $this->server);

        if (isset($this->envVars[$index])) {
            array_splice($this->envVars, $index, 1);
            $this->save();
            $this->dispatch('success', 'Environment variable removed.');
        }
    }

    public function updateEnvVar(int $index): void
    {
        $this->authorize('update', $this->server);

        if (isset($this->envVars[$index])) {
            $this->save();
            $this->dispatch('success', 'Environment variable updated.');
        }
    }

    private function save(): void
    {
        $this->server->predefined_env_vars = array_values($this->envVars);
        $this->server->save();
    }

    public function render()
    {
        return view('livewire.server.env-vars');
    }
}

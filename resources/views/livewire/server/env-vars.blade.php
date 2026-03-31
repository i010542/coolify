<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Environment Variables | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div class="flex flex-col h-full gap-8 sm:flex-row">
        <x-server.sidebar :server="$server" activeMenu="env-vars" />
        <div class="w-full">
            <div class="flex items-center gap-2 mb-4">
                <h2>Predefined Environment Variables</h2>
            </div>
            <div class="mb-6 text-sm text-neutral-500">
                Define environment variables that will be automatically injected into all resources deployed on this server.
                These variables complement application-level variables and are useful for server identification (e.g., <code class="text-xs bg-neutral-100 dark:bg-neutral-800 px-1 rounded">SERVER_NAME</code>, <code class="text-xs bg-neutral-100 dark:bg-neutral-800 px-1 rounded">REGION</code>).
            </div>

            {{-- Add new variable form --}}
            <div class="flex flex-col gap-3 mb-6 p-4 border rounded-lg border-neutral-200 dark:border-neutral-700">
                <h3 class="text-sm font-medium">Add Variable</h3>
                <div class="flex flex-wrap gap-3 items-end">
                    <x-forms.input
                        id="newKey"
                        label="Key"
                        placeholder="SERVER_NAME"
                        helper="Variable name. Use uppercase letters, digits, and underscores."
                        class="flex-1 min-w-[160px]"
                    />
                    <x-forms.input
                        id="newValue"
                        label="Value"
                        placeholder="server-01"
                        class="flex-1 min-w-[160px]"
                    />
                    <x-forms.button wire:click="addEnvVar" class="mb-0.5">
                        Add
                    </x-forms.button>
                </div>
            </div>

            {{-- Existing variables --}}
            @if (count($envVars) > 0)
                <div class="flex flex-col gap-2">
                    <h3 class="text-sm font-medium mb-2">Current Variables</h3>
                    @foreach ($envVars as $index => $envVar)
                        <div class="flex flex-wrap gap-3 items-center p-3 border rounded-lg border-neutral-200 dark:border-neutral-700">
                            <x-forms.input
                                id="envVars.{{ $index }}.key"
                                label="Key"
                                class="flex-1 min-w-[160px]"
                                wire:blur="updateEnvVar({{ $index }})"
                            />
                            <x-forms.input
                                id="envVars.{{ $index }}.value"
                                label="Value"
                                class="flex-1 min-w-[160px]"
                                wire:blur="updateEnvVar({{ $index }})"
                            />
                            <button
                                wire:click="removeEnvVar({{ $index }})"
                                wire:confirm="Are you sure you want to remove this variable?"
                                class="text-red-500 hover:text-red-700 transition-colors mb-0.5 p-1"
                                title="Remove variable"
                            >
                                <x-icons.trash class="w-5 h-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-neutral-400 italic">
                    No predefined environment variables configured. Add one above.
                </div>
            @endif
        </div>
    </div>
</div>

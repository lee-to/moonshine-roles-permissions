<?php

namespace Sweet1s\MoonshineRBAC\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\{confirm, info, intro, select, text};

class MoonShineRBACPermissionCustomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moonshine-rbac:permission {permission? : The name of the permission} {guard? : The name of the guard}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command specifically creates a unique permission named Custom.{permission}, it does not create by abilities (view, viewAny, ...).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        intro($this->description);

        $permission = $this->argument('permission') ?? text(
            label: 'The name of the permission',
            placeholder: 'E.g. MyPermission',
            required: true,
        );

        $guard = $this->argument('guard') ?? text(
            label: 'The name of the guard',
            default: config('moonshine.auth.guard')
        );

        $permission = "Custom.{$permission}";

        $config_model_permission = config('permission.models.permission');

        if (count(explode('.', $permission)) > 2) {
            info("Permission {$permission} is not valid, it must not contain more than one dot.");
            return self::FAILURE;
        }

        $config_model_permission::updateOrCreate([
            'name' => $permission,
            'guard_name' => $guard
        ]);

        info("Permission {$permission} is created");

        app()['cache']->forget('spatie.permission.cache');

        $assign = confirm(
            label: 'Do you want to assign it to a role?',
            default: false
        );

        if ($assign) {
            info("Assign a {$permission} permission to a role:");

            $config_model_role = config('permission.models.role');

            $role_id = select(
                'Select role',
                $config_model_role::pluck('name', 'id'),
            );

            $role = $config_model_role::findOrFail($role_id);

            $role->givePermissionTo($permission);

            info("Permission {$permission} is assigned to role {$role->name}");
        }

        return self::SUCCESS;
    }
}

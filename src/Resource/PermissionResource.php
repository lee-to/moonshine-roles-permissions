<?php

declare(strict_types=1);

namespace Sweet1s\MoonshineRBAC\Resource;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Components\MoonShineComponent;
use MoonShine\Decorations\Block;
use MoonShine\Fields\Field;
use MoonShine\Fields\ID;
use MoonShine\Fields\Select;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;
use Sweet1s\MoonshineRBAC\Abilities;
use Sweet1s\MoonshineRBAC\Traits\WithRolePermissions;

class PermissionResource extends ModelResource
{
    use WithRolePermissions;

    protected string $title = 'Permissions';

    public function __construct()
    {
        $this->model = config('permission.models.permission');
    }

    public function getTitle(): string
    {
        return trans('moonshine-rbac::ui.permissions');
    }

    /**
     * @return list<MoonShineComponent|Field>
     */
    public function fields(): array
    {
        $guards = [];

        collect(array_keys(config('auth.guards')))->map(function ($guard) use (&$guards) {
            $guards[$guard] = $guard;
        });

        $abilities = [];

        foreach (Abilities::getAbilities() as $ability) {
            $abilities[$ability] = $ability;
        }

        $resources = [];

        foreach (moonshine()->getResources() as $resource) {
            $resources['Resources'][class_basename($resource)] = class_basename($resource);
        }

        $resources['Custom Permission']['Custom'] = 'Custom';

        $permissionNameArray = $this->getItem()?->name ? explode('.', $this->getItem()->name) : '';

        return [
            Block::make([
                ID::make()->sortable(),
                Text::make('Name')
                    ->hideOnForm(),

                Text::make('Permission Name', 'permission_name')
                    ->customAttributes([
                        'class' => 'permission_name',
                        'x-init' => isset($permissionNameArray[0]) && $permissionNameArray[0] !== 'Custom'
                            ? $this->hideElement('.permission_name')
                            : (isset($permissionNameArray[0]) ? $this->showElement('.permission_name') : $this->hideElement('.permission_name'))
                    ])
                    ->hideOnIndex()
                    ->hideOnDetail()
                    ->canApply(false)
                    ->default($permissionNameArray[1] ?? ''),
                Select::make('Resource', 'resource')
                    ->customAttributes([
                        '@change' => "(event) => {
                            event.target.value !== 'Custom'
                            ? {$this->showElement('.ability')}
                            : {$this->hideElement('.ability')};

                            event.target.value === 'Custom'
                            ? {$this->showElement('.permission_name')}
                            : {$this->hideElement('.permission_name')}
                        }",
                    ])
                    ->searchable()
                    ->default($permissionNameArray[0] ?? '')
                    ->hideOnIndex()
                    ->hideOnDetail()
                    ->canApply(false)
                    ->options($resources),
                Select::make('Ability', 'ability')
                    ->customAttributes([
                        'class' => 'ability',
                        'x-init' => isset($permissionNameArray[0]) && $permissionNameArray[0] === 'Custom'
                            ? $this->hideElement('.ability')
                            : (isset($permissionNameArray[0]) ? $this->showElement('.ability') : $this->hideElement('.ability'))
                    ])
                    ->searchable()
                    ->hideOnIndex()
                    ->hideOnDetail()
                    ->default($permissionNameArray[1] ?? '')
                    ->canApply(false)
                    ->options($abilities),

                Select::make('Guard', 'guard_name')
                    ->searchable()
                    ->options($guards),
            ]),
        ];
    }

    public function search(): array
    {
        return [
            'name'
        ];
    }

    protected function beforeCreating(Model $item): Model
    {
        $item->name = moonshineRequest()->get('resource') . '.' . moonshineRequest()->get('ability');

        if (moonshineRequest()->has('resource') && moonshineRequest()->get('resource') == 'Custom') {
            $item->name = 'Custom.' . moonshineRequest()->get('permission_name');
        }


        return $item;
    }

    protected function beforeUpdating(Model $item): Model
    {
        return $this->beforeCreating($item);
    }

    public function rules(Model $item): array
    {
        return [];
    }

    private function hideElement(string $class): string
    {
        return "document.querySelector('$class').closest('.moonshine-field').setAttribute('style', 'display: none !important;')";
    }

    private function showElement(string $class): string
    {
        return "document.querySelector('$class').closest('.moonshine-field').setAttribute('style', 'display: block !important;')";
    }
}

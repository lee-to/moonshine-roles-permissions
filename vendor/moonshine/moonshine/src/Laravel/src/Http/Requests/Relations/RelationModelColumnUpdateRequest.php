<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Http\Requests\Relations;

use MoonShine\Core\Exceptions\ResourceException;
use MoonShine\Laravel\Enums\Ability;
use MoonShine\Laravel\Enums\Action;
use Throwable;

class RelationModelColumnUpdateRequest extends RelationModelFieldRequest
{
    /**
     * @throws Throwable
     */
    public function authorize(): bool
    {
        $resource = $this->getField()?->getResource();

        if (\is_null($resource)) {
            throw ResourceException::notDeclared();
        }

        if (! $resource->hasAction(Action::UPDATE)) {
            return false;
        }

        return $resource->can(Ability::UPDATE);
    }

    protected function prepareForValidation(): void
    {
        request()->merge([
            request()->getScalar('field') => request()->getScalar('value'),
        ]);
    }
}

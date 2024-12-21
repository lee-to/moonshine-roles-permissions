<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Resources;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Tests\Fixtures\Models\Cover;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;

class TestCoverResource extends AbstractTestingResource
{
    public string $model = Cover::class;

    public string $title = 'Covers';

    public array $with = ['category'];

    protected function indexFields(): iterable
    {
        return [
            ID::make('ID'),
            Image::make('Image title', 'image'),
            BelongsTo::make('Category title', 'category', 'name', TestCategoryResource::class),
        ];
    }

    protected function formFields(): iterable
    {
        return $this->indexFields();
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function rules(mixed $item): array
    {
        return [];
    }
}

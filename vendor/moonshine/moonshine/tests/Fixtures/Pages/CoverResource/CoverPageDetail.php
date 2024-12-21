<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Pages\CoverResource;

use MoonShine\Laravel\Pages\Crud\DetailPage;

class CoverPageDetail extends DetailPage
{
    protected function fields(): iterable
    {
        return [];
    }

    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}

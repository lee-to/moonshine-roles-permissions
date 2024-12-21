<?php

declare(strict_types=1);

namespace MoonShine\Tests\Fixtures\Resources;

use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;

class TestResourceBuilder
{
    public static function new(string $model = null): TestResource
    {
        $resource = app(TestResource::class);

        if ($model) {
            $resource->setTestModel($model);
        }

        moonshine()->resources([$resource]);

        return $resource;
    }

    public static function testResourceWithAllFeatures(): TestResource
    {
        return self::new(
            MoonshineUser::class
        )
            ->setTestFields([
                Text::make('Name'),
                Email::make('Email'),
                Password::make('Password'),
                Preview::make('Badge')->badge(static fn () => 'red'),
            ])
            ->setTestQueryTags([
                QueryTag::make(
                    'Item #1 Query Tag',
                    static fn ($query) => $query->where('id', 1) // Query builder
                ),
            ])
            ->setTestButtons([
                ActionButton::make(
                    'Test button',
                    url: static fn (): string => '/'
                )->showInLine(),
            ])
            ->setTestMetrics([
                ValueMetric::make('TestValueMetric')->value(MoonshineUser::query()->count()),
            ])
            ->setTestTdAttributes(static fn (
                mixed $data,
                int $row,
                int $cell,
            ) => [
                'data-test-td-attr' => 'success',
            ])->setTestTrAttributes(static function (
                mixed $data,
                int $row,
            ): array {
                if ($row === 1) {
                    return [
                        'data-test-tr-attr' => 'success',
                    ];
                }

                return [];
            });
    }
}

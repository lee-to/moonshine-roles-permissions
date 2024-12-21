<?php

declare(strict_types=1);

use MoonShine\Laravel\Contracts\Fields\HasAsyncSearchContract;
use MoonShine\Laravel\Contracts\Fields\HasRelatedValuesContact;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\ModelRelationField;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\UI\Contracts\DefaultValueTypes\CanBeObject;
use MoonShine\UI\Contracts\HasDefaultValueContract;

uses()->group('model-relation-fields');
uses()->group('belongs-to-field');

beforeEach(function (): void {
    $this->user = MoonshineUser::factory()
        ->count(5)
        ->create();

    $this->item = Item::factory()
        ->create();

    $this->field = BelongsTo::make('User', resource: MoonShineUserResource::class);
});

describe('common field methods', function () {
    it('ModelRelationField is parent', function (): void {
        expect($this->field)
            ->toBeInstanceOf(ModelRelationField::class);
    });

    it('type', function (): void {
        expect($this->field->getAttributes()->get('type'))
            ->toBeEmpty();
    });

    it('correct interfaces', function (): void {
        expect($this->field)
            ->toBeInstanceOf(HasAsyncSearchContract::class)
            ->toBeInstanceOf(HasRelatedValuesContact::class)
            ->toBeInstanceOf(HasDefaultValueContract::class)
            ->toBeInstanceOf(CanBeObject::class);
    });
});

describe('unique field methods', function () {
    it('async search', function (): void {
        expect($this->field->asyncSearch('name'))
            ->isAsyncSearch()
            ->toBeTrue()
            ->getAsyncSearchColumn()
            ->toBe('name');
    });
});

describe('basic methods', function () {
    it('change preview', function () {
        expect($this->field->changePreview(static fn () => 'changed'))
            ->preview()
            ->toBe('changed');
    });

    it('formatted value', function () {
        $field = BelongsTo::make('User', formatted: static fn () => ['changed'], resource: MoonShineUserResource::class)
            ->fillData($this->item);

        expect($field->toFormattedValue())
            ->toBe(['changed']);
    });

    it('applies', function () {
        expect()
            ->applies($this->field);
    });
});

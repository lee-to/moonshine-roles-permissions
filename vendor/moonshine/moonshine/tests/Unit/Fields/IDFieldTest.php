<?php

declare(strict_types=1);

use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;

uses()->group('fields');

beforeEach(function (): void {
    $this->field = ID::make();
});

it('text is parent', function (): void {
    expect($this->field)
        ->toBeInstanceOf(Hidden::class);
});

it('type', function (): void {
    expect($this->field->getAttributes()->get('type'))
        ->toBe('hidden');
});

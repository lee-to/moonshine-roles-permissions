<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Text;

uses()->group('fields');

beforeEach(function (): void {
    $this->field = Email::make('Email');
});

it('text field is parent', function (): void {
    expect($this->field)
        ->toBeInstanceOf(Text::class);
});

it('type', function (): void {
    expect($this->field->getAttributes()->get('type'))
        ->toBe('email');
});

it('view', function (): void {
    expect($this->field->getView())
        ->toBe('moonshine::fields.input');
});

it('apply', function (): void {
    $data = ['email' => 'test@mail.com'];

    fakeRequest(parameters: $data);

    expect(
        $this->field->apply(
            TestResourceBuilder::new()->fieldApply($this->field),
            new class () extends Model {
                protected $fillable = [
                    'email',
                ];
            }
        )
    )
        ->toBeInstanceOf(Model::class)
        ->email
        ->toBe($data['email'])
    ;
});

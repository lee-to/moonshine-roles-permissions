<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestItemResource;
use MoonShine\Tests\Fixtures\Resources\TestResource;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Fields\File;

uses()->group('fields');
uses()->group('file-field');

beforeEach(function () {
    $this->item = createItem(countComments: 0);
    $this->field = File::make('File')->dir('items');
});

it('show field on pages', function () {
    $resource = fileResource()->setTestFields([
        $this->field,
    ]);

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: IndexPage::class, resource: $resource)
    )
        ->assertOk()
        ->assertSee('File')
    ;

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: DetailPage::class, resource: $resource, params: ['resourceItem' => $this->item->getKey()])
    )
        ->assertOk()
        ->assertSee('File')
    ;

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: FormPage::class, resource: $resource, params: ['resourceItem' => $this->item->getKey()])
    )
        ->assertOk()
        ->assertSee('File')
    ;
});

it('apply as base', function () {
    $resource = fileResource()
        ->setTestFields([
            $this->field,
        ]);

    $file = saveFile($resource, $this->item);

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: IndexPage::class, resource: $resource)
    )
        ->assertOk()
        ->assertSee('File')
        ->assertSee('items/' . $file->hashName())
    ;

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: DetailPage::class, resource: $resource, params: ['resourceItem' => $this->item->getKey()])
    )
        ->assertOk()
        ->assertSee('items/' . $file->hashName())
    ;

    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: FormPage::class, resource: $resource, params: ['resourceItem' => $this->item->getKey()])
    )
        ->assertOk()
        ->assertSee('items/' . $file->hashName())
    ;

});

it('file exists after save', function () {
    $resource = fileResource();
    $file = saveFile($resource, $this->item);

    asAdmin()->put(
        $resource->getRoute('crud.update', $this->item->getKey()),
        ['hidden_file' => 'items/' . $file->hashName()]
    )
        ->assertRedirect();

    $this->item->refresh();

    expect($this->item->file)
        ->toBe('items/' . $file->hashName())
    ;

    Storage::disk('public')->assertExists('items/' . $file->hashName());
});

it('before apply', function () {
    $resource = fileResource();

    $file = saveFile($resource, $this->item);

    $file2 = UploadedFile::fake()->create('test2.csv');

    $data = ['file' => $file2];

    asAdmin()->put(
        $resource->getRoute('crud.update', $this->item->getKey()),
        $data
    )
        ->assertRedirect();

    $this->item->refresh();

    expect($this->item->file)
        ->toBe('items/' . $file2->hashName())
    ;

    Storage::disk('public')->assertExists('items/' . $file2->hashName());

    Storage::disk('public')->assertMissing('items/' . $file->hashName());
});

it('after destroy', function () {
    $resource = fileResource();

    $file = saveFile($resource, $this->item);

    asAdmin()->delete(
        $resource->getRoute('crud.destroy', $this->item->getKey()),
    )
        ->assertRedirect();

    Storage::disk('public')->assertMissing('items/' . $file->hashName());
});

it('after destroy disableDeleteFiles', function () {
    $resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ...app(TestItemResource::class)->getFormFields()->toArray(),
            File::make('File')
                ->disableDeleteFiles()
                ->dir('items'),
        ]);

    $file = saveFile($resource, $this->item);

    asAdmin()->delete(
        $resource->getRoute('crud.destroy', $this->item->getKey()),
    )
        ->assertRedirect();

    Storage::disk('public')->assertExists('items/' . $file->hashName());
});

function fileResource(): TestResource
{
    return addFieldsToTestResource(
        File::make('File')->dir('items'),
    );
}

function saveFile(ModelResource $resource, Model $item)
{
    $file = UploadedFile::fake()->create('test.csv');

    $data = ['file' => $file];

    asAdmin()->put(
        $resource->getRoute('crud.update', $item->getKey()),
        $data
    )
        ->assertRedirect();

    $item->refresh();

    expect($item->file)
        ->toBe('items/' . $file->hashName())
    ;

    Storage::disk('public')->assertExists('items/' . $file->hashName());

    return $file;
}

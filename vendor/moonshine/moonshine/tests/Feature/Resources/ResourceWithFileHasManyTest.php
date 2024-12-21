<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Tests\Fixtures\Models\Item;
use MoonShine\Tests\Fixtures\Resources\TestFileResource;
use MoonShine\Tests\Fixtures\Resources\TestFileResourceWithParent;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

uses()->group('resources-feature');
uses()->group('resources-has-many-files');

beforeEach(function (): void {

    $this->item = createItem(1, 1);

    $this->resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            HasMany::make('Files', 'itemFiles', resource: TestFileResource::class),
        ])
    ;
});

it('resource with has many', function () {
    asAdmin()->get(
        $this->moonshineCore->getRouter()->getEndpoints()->toPage(page: FormPage::class, resource: $this->resource, params: ['resourceItem' => $this->item->id])
    )
        ->assertOk()
        ->assertSee('Name')
        ->assertSee('Files')
    ;
});

it('delete a has many file after delete item', function () {
    $file = addHasManyFile(app(TestFileResource::class), $this->item);

    $this->resource->setDeleteRelationships();

    deleteItemWithHasMany($this->resource, $this->item->getKey());

    Storage::disk('public')->assertMissing($file->hashName());

});

it('not delete a has many file after delete item', function () {
    $file = addHasManyFile(app(TestFileResource::class), $this->item);

    deleteItemWithHasMany($this->resource, $this->item->getKey());

    Storage::disk('public')->assertExists($file->hashName());
});

it('delete a has many file after delete item with parent', function () {

    $fileResource = app(TestFileResourceWithParent::class);

    $resource = TestResourceBuilder::new(Item::class)
        ->setTestFields([
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            HasMany::make('Files', 'itemFiles', resource: $fileResource),
        ])
    ;

    $file = UploadedFile::fake()->create('test.csv');

    $data = [
        'path' => $file,
        'item_id' => $this->item->id,
    ];

    asAdmin()->post(
        $fileResource->getRoute('crud.store', $this->item->getKey()),
        $data
    )
        ->assertRedirect();

    $this->item->refresh();

    $filePath = 'item_files/' . $this->item->id . '/' . $file->hashName();

    expect($this->item->itemFiles)
        ->not()->toBeNull()
        ->and($this->item->itemFiles->first()->path)
        ->toBe($filePath)
    ;

    Storage::disk('public')->assertExists($filePath);

    $this->resource->setDeleteRelationships();

    deleteItemWithHasMany($resource, $this->item->getKey());

    Storage::disk('public')->assertMissing($filePath);
});

function addHasManyFile(ModelResource $resource, Model $item)
{
    $file = UploadedFile::fake()->create('test.csv');

    $data = [
        'path' => $file,
        'item_id' => $item->id,
    ];

    asAdmin()->post(
        $resource->getRoute('crud.store', $item->getKey()),
        $data
    )
        ->assertRedirect();

    $item->refresh();

    expect($item->itemFiles)
        ->not()->toBeNull()
        ->and($item->itemFiles->first()->path)
        ->toBe($file->hashName())
    ;

    Storage::disk('public')->assertExists($file->hashName());

    return $file;
}

function deleteItemWithHasMany(ModelResource $resource, int $itemId): void
{
    asAdmin()->delete(
        $resource->getRoute('crud.destroy', $itemId),
    )
        ->assertRedirect()
    ;

    $item = Item::query()->where('id', $itemId)->first();

    expect($item)->toBeNull();
}

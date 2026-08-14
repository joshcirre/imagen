<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    Storage::fake('local');
    config(['filesystems.default' => 'local']);
});

test('an image can be saved and loaded by everyone', function (): void {
    $image = UploadedFile::fake()->createWithContent(
        'Taylor Portrait.png',
        file_get_contents(public_path('img/laravel-cloud-logo.png')),
    );

    $response = post(route('shared-images.store'), [
        'images' => [$image],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.0.name', 'Taylor Portrait')
        ->assertJsonPath('data.0.isSaved', true);

    $paths = Storage::disk('local')->allFiles('shared-images');

    expect($paths)->toHaveCount(1)
        ->and($paths[0])->toEndWith('/Taylor Portrait.png');

    get(route('shared-images.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Taylor Portrait')
        ->assertJsonPath('data.0.isSaved', true);

    get($response->json('data.0.src'))
        ->assertSuccessful()
        ->assertHeader('content-type', 'image/png');
});

test('only supported images can be saved', function (): void {
    $this->withHeader('Accept', 'application/json')
        ->post(route('shared-images.store'), [
            'images' => [UploadedFile::fake()->create('notes.txt', 1, 'text/plain')],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');

    Storage::disk('local')->assertDirectoryEmpty('shared-images');
});

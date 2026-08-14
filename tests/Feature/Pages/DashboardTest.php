<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the image studio is the public main page', function (): void {
    get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Create share-ready graphics.')
        ->assertSee('Laravel Cloud')
        ->assertSee('Drop people PNGs')
        ->assertSee('Drop approved artwork')
        ->assertSee('Export PNG')
        ->assertSee('data-image-studio-canvas', false)
        ->assertSee('data-draggable-copy', false)
        ->assertSee('x-on:pointerdown.prevent="startCopyDrag"', false)
        ->assertSee('Reset headline position')
        ->assertSee('x-bind:style="artboardStyle"', false)
        ->assertSee('data-upload-dropzone="background"', false)
        ->assertSee('data-upload-dropzone="people"', false)
        ->assertSee('data-flux-file-upload', false)
        ->assertSee('/img/laravel-cloud.svg', false)
        ->assertSee('/img/laravel.svg', false)
        ->assertSee('data-person-layer="behind"', false)
        ->assertSee('data-person-layer="above"', false)
        ->assertSee('data-resize-handle="top-left"', false)
        ->assertSee('data-resize-handle="middle-left"', false)
        ->assertSee('data-resize-handle="bottom-right"', false)
        ->assertSee('aria-label="Image scale"', false)
        ->assertSee('max="200"', false)
        ->assertDontSee('artboard__eyebrow', false)
        ->assertDontSee('artboard__edition', false);
});

test('former application pages are not available', function (string $path): void {
    get($path)->assertNotFound();
})->with([
    '/auth/login',
    '/auth/register',
    '/auth/forgot-password',
    '/profile',
    '/playground',
    '/verify-email',
]);

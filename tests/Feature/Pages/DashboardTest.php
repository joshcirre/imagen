<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the image studio is the public main page', function (): void {
    $studioStyles = file_get_contents(resource_path('css/app.css'));
    $studioScript = file_get_contents(resource_path('js/image-studio.js'));
    $cloudLogo = getimagesize(public_path('img/laravel-cloud-logo.png'));
    $cloudBackground = getimagesize(public_path('img/laravel-cloud-background.png'));
    $cloudOpenGraphBackground = getimagesize(public_path('img/laravel-cloud-og-background.png'));
    $laravelLogo = getimagesize(public_path('img/laravel-logo.png'));
    $laravelOpenGraphBackground = getimagesize(public_path('img/laravel-og-background.png'));

    expect($studioStyles)->toContain("font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;")
        ->toContain('--artboard-ink: #fff;')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*font-weight: 400;/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*font-size: calc\(6\.640625cqw \* var\(--headline-scale, 1\)\);/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*line-height: 1;/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*letter-spacing: 0;/s')
        ->toContain("url('/img/laravel-cloud-background.png')")
        ->toContain('.artboard__safe-area--mobile')
        ->toContain('right: 3.203125%;')
        ->toContain('width: 7.8125%;')
        ->toContain('height: 59.305556%;')
        ->not->toContain('letter-spacing: -0.04em;')
        ->not->toContain('letter-spacing: -0.065em;')
        ->not->toContain('.artboard__rule')
        ->and($studioScript)->toContain("'person-text': {")
        ->toContain("'person-code': {")
        ->toContain("'custom-visual': {")
        ->toContain("'text-only': {")
        ->toContain('resetTemplate()')
        ->toContain('template reset to its Figma defaults')
        ->toContain('sanitizeExportClone(clone)')
        ->toContain("attributeName.startsWith('x-')")
        ->toContain('onCloneEachNode: (clone) => this.sanitizeExportClone(clone)')
        ->and(public_path('img/laravel-cloud-logo.png'))->toBeFile()
        ->and($cloudLogo)->not->toBeFalse()
        ->and($cloudLogo[0])->toBe(350)
        ->and($cloudLogo[1])->toBe(36)
        ->and(public_path('img/laravel-cloud-background.png'))->toBeFile()
        ->and($cloudBackground)->not->toBeFalse()
        ->and($cloudBackground[0])->toBe(1280)
        ->and($cloudBackground[1])->toBe(720)
        ->and(public_path('img/laravel-cloud-og-background.png'))->toBeFile()
        ->and($cloudOpenGraphBackground)->not->toBeFalse()
        ->and($cloudOpenGraphBackground[0])->toBe(1200)
        ->and($cloudOpenGraphBackground[1])->toBe(630)
        ->and(public_path('img/laravel-logo.png'))->toBeFile()
        ->and($laravelLogo)->not->toBeFalse()
        ->and($laravelLogo[0])->toBe(184)
        ->and($laravelLogo[1])->toBe(46)
        ->and(public_path('img/laravel-og-background.png'))->toBeFile()
        ->and($laravelOpenGraphBackground)->not->toBeFalse()
        ->and($laravelOpenGraphBackground[0])->toBe(1800)
        ->and($laravelOpenGraphBackground[1])->toBe(945);

    get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Create share-ready graphics.')
        ->assertSee('Laravel Cloud')
        ->assertSee('Figma')
        ->assertSee('Person + text')
        ->assertSee('Person + code')
        ->assertSee('Custom visual')
        ->assertSee('Text only')
        ->assertSee('Reset template')
        ->assertSee('Safe areas')
        ->assertSee('Mobile UI')
        ->assertSee('Time')
        ->assertSee('Drop image layers')
        ->assertSee('PNG, JPG, or WebP · 15 MB max')
        ->assertSee('Add people, product shots, screenshots, or any other image.')
        ->assertSee('Drop approved artwork')
        ->assertSee('Export PNG')
        ->assertSee('data-image-studio-canvas', false)
        ->assertSee('class="artboard__safe-areas"', false)
        ->assertSee('x-on:click="resetTemplate"', false)
        ->assertSee('x-text="templateSlotLabel"', false)
        ->assertSee('data-draggable-copy', false)
        ->assertSee('x-on:pointerdown.prevent="startCopyDrag"', false)
        ->assertSee('data-draggable-logo', false)
        ->assertSee('x-on:pointerdown.prevent="startLogoDrag"', false)
        ->assertSee('x-bind:style="logoStyle"', false)
        ->assertSee('aria-label="Logo scale"', false)
        ->assertSee('Reset logo')
        ->assertSee('Reset headline position')
        ->assertSee('x-bind:style="artboardStyle"', false)
        ->assertSee('data-upload-dropzone="background"', false)
        ->assertSee('data-upload-dropzone="images"', false)
        ->assertSee('data-flux-file-upload', false)
        ->assertSee('/img/laravel-cloud-logo.png', false)
        ->assertSee('/img/laravel-logo.png', false)
        ->assertSee('accept="image/png,image/jpeg,image/webp"', false)
        ->assertSee('data-image-layer="behind"', false)
        ->assertSee('data-image-layer="above"', false)
        ->assertSee('data-resize-handle="top-left"', false)
        ->assertSee('data-resize-handle="middle-left"', false)
        ->assertSee('data-resize-handle="bottom-right"', false)
        ->assertSee('aria-label="Image scale"', false)
        ->assertSee('max="200"', false)
        ->assertSee('w-full!', false)
        ->assertDontSee('Live canvas')
        ->assertDontSee('status-dot', false)
        ->assertDontSee('artboard__eyebrow', false)
        ->assertDontSee('artboard__edition', false)
        ->assertDontSee('artboard__rule', false);
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

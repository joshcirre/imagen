<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the image studio is the public main page', function (): void {
    $studioStyles = file_get_contents(resource_path('css/app.css'));
    $studioScript = file_get_contents(resource_path('js/image-studio.js'));
    $cloudBackground = getimagesize(public_path('img/laravel-cloud-background.png'));
    $templatePreviews = [
        'cloud-bill',
        'starter-kits',
        'cloud-ama',
    ];
    $templateAssets = [
        'cloud-bill-logo.svg',
        'cloud-ama-logo.svg',
    ];
    $fontAssets = [
        'instrument-sans-latin.woff2',
        'instrument-sans-latin-ext.woff2',
    ];

    expect($studioStyles)->toContain("font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;")
        ->toContain('--artboard-ink: #fff;')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*font-weight: 400;/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*font-size: calc\(6\.640625cqw \* var\(--headline-scale, 1\)\);/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*line-height: 1;/s')
        ->toMatch('/\.artboard__copy h2\s*\{[^}]*letter-spacing: 0;/s')
        ->toMatch("/font-feature-settings:\\s*'ss02' 1,\\s*'ss04' 1,\\s*'ss05' 1;/s")
        ->toContain('--studio-accent: #0057ff;')
        ->toContain('--studio-ink: #11181c;')
        ->toContain("url('/img/laravel-cloud-background.png')")
        ->toContain('.artboard__safe-area--mobile')
        ->toContain('right: 3.203125%;')
        ->toContain('width: 7.8125%;')
        ->toContain('height: 59.305556%;')
        ->toContain("url('/fonts/instrument-sans-latin.woff2') format('woff2')")
        ->not->toContain("font-family: 'Instrument Serif'")
        ->not->toContain("font-family: 'Rajdhani'")
        ->not->toContain('laravel-cloud-og-background.png')
        ->not->toContain('laravel-og-background.png')
        ->toContain('.artboard--starter-kits .artboard__template-word')
        ->toContain('.artboard--cloud-bill .artboard__copy')
        ->toContain('.artboard--cloud-ama .artboard__headline-row')
        ->not->toContain('.artboard--whats-new')
        ->not->toContain('.artboard--nightwatch-ama')
        ->not->toContain('.artboard--person-text')
        ->not->toContain('.artboard--person-code')
        ->not->toContain('.artboard--cloud-comparison')
        ->not->toContain('.artboard__rule')
        ->and($studioScript)->toContain("'cloud-bill': {")
        ->toContain("'starter-kits': {")
        ->toContain("'cloud-ama': {")
        ->toContain("label: 'Announcement'")
        ->toContain("label: 'Product'")
        ->toContain("label: 'Interview'")
        ->not->toContain("'whats-new': {")
        ->not->toContain("'nightwatch-ama': {")
        ->not->toContain("'person-text': {")
        ->not->toContain("'person-code': {")
        ->not->toContain("'cloud-comparison': {")
        ->toContain("figmaNode: '5:272000'")
        ->toContain("figmaNode: '27:217728'")
        ->toContain("figmaNode: '27:218797'")
        ->toContain("fontLabel: 'Instrument Sans Medium'")
        ->not->toContain('Open Graph')
        ->not->toContain("format: 'thumbnail'")
        ->toContain('resetTemplate()')
        ->toContain('template reset to its defaults')
        ->toContain('sanitizeExportClone(clone)')
        ->toContain("attributeName.startsWith('x-')")
        ->toContain('createExportCanvas(canvas)')
        ->toContain('data-image-studio-export-canvas')
        ->toContain('exportCanvas?.remove()')
        ->toContain('exportScale: 2')
        ->not->toContain('exportScale: 1')
        ->toContain('scale: this.selectedFormat.exportScale')
        ->toContain('get exportWidth()')
        ->toContain('get exportHeight()')
        ->toContain('onCloneEachNode: (clone) => this.sanitizeExportClone(clone)')
        ->and(public_path('img/laravel-cloud-background.png'))->toBeFile()
        ->and($cloudBackground)->not->toBeFalse()
        ->and($cloudBackground[0])->toBe(1280)
        ->and($cloudBackground[1])->toBe(720)
        ->and(public_path('img/laravel-cloud-og-background.png'))->not->toBeFile()
        ->and(public_path('img/laravel-og-background.png'))->not->toBeFile();

    foreach ($templatePreviews as $templatePreview) {
        $previewPath = public_path("img/youtube-templates/{$templatePreview}-preview.png");
        $previewSize = getimagesize($previewPath);

        expect($previewPath)->toBeFile()
            ->and($previewSize)->not->toBeFalse()
            ->and($previewSize[0])->toBe(1280)
            ->and($previewSize[1])->toBe(720);
    }

    foreach ($templateAssets as $templateAsset) {
        expect(public_path("img/youtube-templates/{$templateAsset}"))->toBeFile();
    }

    foreach ($fontAssets as $fontAsset) {
        expect(public_path("fonts/{$fontAsset}"))->toBeFile();
    }

    get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Create share-ready graphics.')
        ->assertSee('3 essentials')
        ->assertSee('Choose the content shape. Each layout keeps its approved Figma composition.')
        ->assertSee('Announcement')
        ->assertSee('Product')
        ->assertSee('Interview')
        ->assertDontSee('Open Graph')
        ->assertDontSee("What's New", false)
        ->assertDontSee('Nightwatch')
        ->assertDontSee('Person + text')
        ->assertDontSee('Person + code')
        ->assertDontSee('Cloud vs Vapor')
        ->assertDontSee('Nightwatch AMA')
        ->assertSee('data-figma-node="5:272000"', false)
        ->assertSee('data-figma-node="27:217728"', false)
        ->assertSee('data-figma-node="27:218797"', false)
        ->assertDontSee('data-format=', false)
        ->assertSee('Reset template')
        ->assertSee('Safe areas')
        ->assertSee('Mobile UI')
        ->assertSee('Time')
        ->assertSee('Drop image layers')
        ->assertSee('PNG, JPG, or WebP · 15 MB max')
        ->assertSee('Save for everyone')
        ->assertSee('Save once to share it with everyone.')
        ->assertSee('data-shared-images-index-url', false)
        ->assertSee('data-shared-images-store-url', false)
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
        ->assertDontSee('/img/laravel-cloud-og-background.png', false)
        ->assertDontSee('/img/laravel-og-background.png', false)
        ->assertDontSee('/img/youtube-templates/cloud-bill-product.png', false)
        ->assertDontSee('/img/youtube-templates/starter-dashboard.png', false)
        ->assertDontSee('/img/youtube-templates/starter-register.png', false)
        ->assertDontSee('/img/youtube-templates/starter-kit-cubes.png', false)
        ->assertDontSee('/img/youtube-templates/cloud-ama-background.png', false)
        ->assertDontSee('/img/youtube-templates/cloud-ama-watermark.svg', false)
        ->assertDontSee('/img/youtube-templates/nightwatch-dashboard.png', false)
        ->assertDontSee('/img/youtube-templates/nightwatch-gradient.png', false)
        ->assertDontSee('fonts.googleapis.com', false)
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
        ->assertSee('class="artboard__eyebrow"', false)
        ->assertSee('class="artboard__supporting"', false)
        ->assertSee('class="artboard__template-art"', false)
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

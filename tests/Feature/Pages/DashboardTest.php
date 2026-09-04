<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the image studio is the public main page', function (): void {
    $studioStyles = file_get_contents(resource_path('css/app.css'));
    $studioScript = file_get_contents(resource_path('js/image-studio.js'));
    $cloudBackground = getimagesize(public_path('img/laravel-cloud-background.png'));
    $cloudYoutubeBackground = getimagesize(public_path('img/youtube-templates/laravel-cloud-youtube-background.png'));
    $templatePreviews = [
        'cloud-bill',
        'starter-kits',
        'cloud-ama',
        'laravel-cloud-youtube',
        'whats-new',
        'nightwatch-ama',
        'cloud-comparison',
    ];
    $templateAssets = [
        'cloud-bill-logo.svg',
        'cloud-ama-logo.svg',
        'laravel-cloud-youtube-background.png',
        'laravel-cloud-youtube-logo.svg',
        'whats-new-logo.svg',
        'nightwatch-logo-mark.svg',
        'nightwatch-dashboard.png',
        'nightwatch-gradient.png',
        'comparison-logo.svg',
        'comparison-cloud-mark.svg',
        'comparison-cloud-ui.png',
        'comparison-vapor-mark.svg',
        'comparison-vapor-ui.png',
        'comparison-vs.svg',
    ];
    $fontAssets = [
        'instrument-sans-latin.woff2',
        'instrument-sans-latin-ext.woff2',
        'instrument-serif-italic-latin.woff2',
        'instrument-serif-italic-latin-ext.woff2',
        'instrument-serif-regular-latin.woff2',
        'instrument-serif-regular-latin-ext.woff2',
        'rajdhani-medium-latin.woff2',
        'rajdhani-medium-latin-ext.woff2',
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
        ->toContain("font-family: 'Instrument Serif'")
        ->toContain("font-family: 'Rajdhani'")
        ->not->toContain('laravel-cloud-og-background.png')
        ->not->toContain('laravel-og-background.png')
        ->toContain('.artboard--starter-kits .artboard__template-word')
        ->toContain('.artboard--cloud-bill .artboard__copy')
        ->toContain('.artboard--cloud-ama .artboard__headline-row')
        ->toContain('.template-art--laravel-cloud-youtube')
        ->toContain('.template-art--whats-new')
        ->toContain('.template-art--nightwatch')
        ->toContain('.template-art--cloud-comparison')
        ->toContain("url('/img/youtube-templates/laravel-cloud-youtube-background.png')")
        ->toMatch('/\.artboard--laravel-cloud-youtube \.artboard__copy h2\s*\{[^}]*font-size: calc\(10cqw \* var\(--headline-scale, 1\)\);/s')
        ->toMatch('/\.artboard--laravel-cloud-youtube \.artboard__copy h2\s*\{[^}]*font-weight: 700;/s')
        ->toMatch('/\.artboard--laravel-cloud-youtube \.artboard__copy h2\s*\{[^}]*font-stretch: 75%;/s')
        ->toMatch('/\.artboard--laravel-cloud-youtube \.artboard__copy h2\s*\{[^}]*letter-spacing: -0\.390625cqw;/s')
        ->toContain('.artboard--whats-new')
        ->toContain('.artboard--nightwatch-ama')
        ->not->toContain('.artboard--person-text')
        ->not->toContain('.artboard--person-code')
        ->toContain('.artboard--cloud-comparison')
        ->toContain('.artboard--font-serif .artboard__copy h2')
        ->toContain('.artboard--font-rajdhani .artboard__copy h2')
        ->toMatch('/\.artboard__eyebrow,\s*\.artboard__supporting,\s*\.artboard__template-word,\s*\.artboard__copy h2\s*\{[^}]*white-space: pre-line !important;/s')
        ->not->toContain('.artboard__rule')
        ->and($studioScript)->toContain("'cloud-bill': {")
        ->toContain("'starter-kits': {")
        ->toContain("'cloud-ama': {")
        ->toContain("'laravel-cloud-youtube': {")
        ->toContain("'whats-new': {")
        ->toContain("'nightwatch-ama': {")
        ->toContain("'cloud-comparison': {")
        ->toContain("label: 'Announcement'")
        ->toContain("label: 'Product'")
        ->toContain("label: 'Interview'")
        ->toContain("label: 'Cloud'")
        ->toContain('label: "What\'s New"')
        ->toContain("label: 'Nightwatch'")
        ->toContain("label: 'Cloud vs Vapor'")
        ->not->toContain("'person-text': {")
        ->not->toContain("'person-code': {")
        ->toContain("figmaNode: '5:272000'")
        ->toContain("figmaNode: '27:217728'")
        ->toContain("figmaNode: '27:218797'")
        ->toContain("figmaNode: '4070:370825'")
        ->toContain("figmaNode: '5:9008'")
        ->toContain("figmaNode: '35:244539'")
        ->toContain("figmaNode: '28:220441'")
        ->toContain("fontPreset: 'condensed'")
        ->toContain("fontPreset: 'rajdhani'")
        ->toContain("logo: '/img/youtube-templates/laravel-cloud-youtube-logo.svg'")
        ->toContain("laravel: '/img/laravel-logo.png'")
        ->toContain("cloud: '/img/laravel-cloud-logo.png'")
        ->not->toContain('Open Graph')
        ->not->toContain("format: 'thumbnail'")
        ->toContain('resetTemplate()')
        ->toContain('useBackgroundOnly()')
        ->toContain("this.logoChoice = 'none'")
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
        ->and($cloudYoutubeBackground)->not->toBeFalse()
        ->and($cloudYoutubeBackground[0])->toBe(1280)
        ->and($cloudYoutubeBackground[1])->toBe(720)
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
        ->assertSee('Thumbnail Studio')
        ->assertSee('7 templates')
        ->assertSee('Approved Figma compositions with editable copy, logos, fonts, and image layers.')
        ->assertSee('Announcement')
        ->assertSee('Product')
        ->assertSee('Interview')
        ->assertSee('Cloud')
        ->assertDontSee('Open Graph')
        ->assertSee("What's New", false)
        ->assertSee('Nightwatch')
        ->assertDontSee('Person + text')
        ->assertDontSee('Person + code')
        ->assertSee('Cloud vs Vapor')
        ->assertDontSee('Nightwatch AMA')
        ->assertSee('data-figma-node="5:272000"', false)
        ->assertSee('data-figma-node="27:217728"', false)
        ->assertSee('data-figma-node="27:218797"', false)
        ->assertSee('data-figma-node="4070:370825"', false)
        ->assertSee('data-figma-node="5:9008"', false)
        ->assertSee('data-figma-node="35:244539"', false)
        ->assertSee('data-figma-node="28:220441"', false)
        ->assertSee('/img/youtube-templates/laravel-cloud-youtube-preview.png', false)
        ->assertDontSee('data-format=', false)
        ->assertSee('Use background only')
        ->assertSee('Reset defaults')
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
        ->assertSee('Template logo')
        ->assertSee('No logo')
        ->assertSee('Headline font')
        ->assertSee('Instrument Sans Condensed')
        ->assertSee('Instrument Serif')
        ->assertSee('Rajdhani')
        ->assertSee('Press Enter to add a line break.')
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

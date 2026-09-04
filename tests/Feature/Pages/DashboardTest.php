<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the image studio implements the Laravel Cloud YouTube guardrails', function (): void {
    $studioStyles = file_get_contents(resource_path('css/app.css'));
    $studioScript = file_get_contents(resource_path('js/image-studio.js'));
    $backgroundAssets = [
        'dark' => 'youtube-bg-dark.webp',
        'light' => 'youtube-bg-light.webp',
        'blue' => 'youtube-bg-blue.webp',
    ];

    expect($studioStyles)
        ->toContain("font-family: 'Instrument Sans Condensed';")
        ->toContain("font-family: 'Instrument Sans Condensed', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;")
        ->toContain('font-size: 10cqw;')
        ->toContain('font-weight: 700;')
        ->toContain('line-height: 0.875;')
        ->toContain('letter-spacing: -0.0390625em;')
        ->toContain("font-feature-settings: 'ss05' 1;")
        ->toMatch('/\.artboard__logo\s*\{[^}]*top: 5\.555556%;[^}]*left: 3\.125%;[^}]*width: 20\.3125%;[^}]*height: auto;/s')
        ->toMatch('/\.artboard__copy\s*\{[^}]*bottom: 6\.666667%;[^}]*left: 3\.125%;[^}]*width: 59\.453125%;[^}]*height: 75\.694444%;/s')
        ->toContain('--subject-zone-start: 62.578125%;')
        ->toContain("url('/img/youtube-templates/youtube-bg-dark.webp')")
        ->toContain("url('/img/youtube-templates/youtube-bg-light.webp')")
        ->toContain("url('/img/youtube-templates/youtube-bg-blue.webp')")
        ->toContain('mix-blend-mode: multiply;')
        ->toContain('mix-blend-mode: overlay;')
        ->not->toContain('var(--headline-scale')
        ->not->toContain('.alignment-options')
        ->and($studioScript)
        ->toContain("background: 'blue'")
        ->toContain("figmaNode: '4070:370747'")
        ->toContain("figmaNode: '4070:370786'")
        ->toContain("figmaNode: '4070:370825'")
        ->toContain("asset: '/img/youtube-templates/youtube-bg-dark.webp'")
        ->toContain("logo: '/img/youtube-templates/laravel-cloud-on-dark.svg'")
        ->toContain("logo: '/img/youtube-templates/laravel-cloud-on-light.svg'")
        ->toContain('const subjectZoneStart = 62.578125;')
        ->toContain("file.type !== 'image/png'")
        ->toContain('imageHasTransparency(subjectSource)')
        ->toContain('this.subject.x = this.clamp(this.subject.x, minimumX, 112);')
        ->toContain('exportScale: 2')
        ->toContain('scale: thumbnailFormat.exportScale')
        ->toContain('sanitizeExportClone(clone)')
        ->toContain("attributeName.startsWith('x-')")
        ->toContain('data-image-studio-export-canvas')
        ->not->toContain('headlineSize')
        ->not->toContain('logoScale')
        ->not->toContain('rotation');

    foreach ($backgroundAssets as $variant => $asset) {
        $assetPath = public_path("img/youtube-templates/{$asset}");
        $assetSize = getimagesize($assetPath);

        expect($assetPath)->toBeFile()
            ->and($assetSize)->not->toBeFalse()
            ->and($assetSize[0])->toBe(2560, "The {$variant} background width must remain at the delivered 2× size.")
            ->and($assetSize[1])->toBe(1440, "The {$variant} background height must remain at the delivered 2× size.")
            ->and(filesize($assetPath))->toBeLessThan(150_000);
    }

    foreach (['dark', 'light'] as $surface) {
        $logoPath = public_path("img/youtube-templates/laravel-cloud-on-{$surface}.svg");

        expect($logoPath)->toBeFile()
            ->and(file_get_contents($logoPath))->toContain('viewBox="0 0 197 24"');
    }

    get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('YouTube thumbnail studio')
        ->assertSee('Content is flexible. Brand stays locked.')
        ->assertSee('Thumbnail copy')
        ->assertSee('maxlength="56"', false)
        ->assertSee('Four words or fewer. No trailing punctuation.')
        ->assertSee('Dark')
        ->assertSee('Light')
        ->assertSee('Blue')
        ->assertSee('Blue recommended')
        ->assertSee('data-figma-node="4070:370747"', false)
        ->assertSee('data-figma-node="4070:370786"', false)
        ->assertSee('data-figma-node="4070:370825"', false)
        ->assertSee('Drop subject PNG')
        ->assertSee('Transparent PNG · 15 MB max')
        ->assertSee('accept="image/png"', false)
        ->assertSee('Subject zone')
        ->assertSee('Export PNG')
        ->assertSee('data-image-studio-canvas', false)
        ->assertSee('data-figma-file="VSARPN3yuAv3TICdMX3ZlC"', false)
        ->assertSee('data-upload-dropzone="subject"', false)
        ->assertDontSee('Announcement')
        ->assertDontSee('Product deep dive')
        ->assertDontSee('Interview')
        ->assertDontSee('Generate layout')
        ->assertDontSee('Type scale')
        ->assertDontSee('Logo scale')
        ->assertDontSee('Text alignment')
        ->assertDontSee('Supporting line')
        ->assertDontSee('Drop approved artwork')
        ->assertDontSee('Save for everyone')
        ->assertDontSee('data-upload-dropzone="background"', false)
        ->assertDontSee('data-shared-images-index-url', false)
        ->assertDontSee('data-draggable-copy', false)
        ->assertDontSee('data-draggable-logo', false)
        ->assertDontSee('multiple', false);
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

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSharedImagesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SharedImageController extends Controller
{
    /**
     * @var list<string>
     */
    private const array SUPPORTED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp'];

    public function index(): JsonResponse
    {
        $assets = collect($this->disk()->allFiles('shared-images'))
            ->filter(fn (string $path): bool => in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), self::SUPPORTED_EXTENSIONS, true))
            ->sortDesc()
            ->map(fn (string $path): array => $this->assetData($path))
            ->values();

        return response()->json(['data' => $assets]);
    }

    public function store(StoreSharedImagesRequest $request): JsonResponse
    {
        $folder = 'shared-images/'.now()->format('YmdHisv').'-'.Str::uuid();

        $assets = collect($request->file('images'))
            ->map(function (UploadedFile $image) use ($folder): array {
                $name = $this->displayName($image->getClientOriginalName());
                $filename = $this->safeFilename($name, $image->extension());
                $path = $image->storeAs($folder, $filename, $this->diskName());

                if (! is_string($path)) {
                    throw new RuntimeException('The image could not be saved.');
                }

                return $this->assetData($path, $name);
            })
            ->values();

        return response()->json(['data' => $assets], 201);
    }

    public function show(string $asset): StreamedResponse
    {
        $path = $this->decodePath($asset);

        abort_unless(
            Str::startsWith($path, 'shared-images/') && $this->disk()->exists($path),
            404,
        );

        return $this->disk()->response($path, basename($path), [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * @return array{id: string, name: string, src: string, isSaved: true}
     */
    private function assetData(string $path, ?string $name = null): array
    {
        $encodedPath = mb_rtrim(strtr(base64_encode($path), '+/', '-_'), '=');

        return [
            'id' => hash('sha256', $path),
            'name' => $name ?? $this->displayName(basename($path)),
            'src' => URL::temporarySignedRoute(
                'shared-images.show',
                now()->addHours(12),
                ['asset' => $encodedPath],
            ),
            'isSaved' => true,
        ];
    }

    private function decodePath(string $asset): string
    {
        $encodedPath = strtr($asset, '-_', '+/');
        $encodedPath = mb_str_pad($encodedPath, mb_strlen($encodedPath) + ((4 - mb_strlen($encodedPath) % 4) % 4), '=', STR_PAD_RIGHT);

        $path = base64_decode($encodedPath, true);

        abort_unless(is_string($path), 404);

        return $path;
    }

    private function displayName(string $filename): string
    {
        $name = Str::of(pathinfo($filename, PATHINFO_FILENAME))
            ->replace(['-', '_'], ' ')
            ->squish()
            ->limit(100, '')
            ->toString();

        return $name === '' ? 'Image' : $name;
    }

    private function safeFilename(string $name, string $extension): string
    {
        $safeName = Str::of($name)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9 _-]/', '')
            ->squish()
            ->limit(100, '')
            ->toString();

        return ($safeName === '' ? 'Image' : $safeName).'.'.$extension;
    }

    private function diskName(): string
    {
        return (string) config('filesystems.default');
    }

    private function disk(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk($this->diskName());
    }
}

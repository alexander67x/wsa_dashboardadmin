<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoDownloadController extends Controller
{
    public function __invoke(Request $request, int $archivo): StreamedResponse
    {
        $record = Archivo::query()->findOrFail($archivo);
        $url = $record->getDownloadUrl() ?? $record->ruta_storage;

        if ($url && str_contains($url, 'res.cloudinary.com/')) {
            $url = $this->buildPrivateCloudinaryDownloadUrl($record->ruta_storage) ?? $url;
        }

        if (! $url) {
            abort(404);
        }

        $response = Http::withOptions(['stream' => true])->get($url);

        if (! $response->ok()) {
            abort(502, 'No se pudo obtener el archivo.');
        }

        $body = $response->toPsrResponse()->getBody();
        $contentType = $record->tipo_mime ?: $response->header('Content-Type') ?: 'application/octet-stream';
        $filename = $this->resolveFilename($record->nombre_original, $url, $contentType);

        return response()->streamDownload(function () use ($body) {
            while (! $body->eof()) {
                echo $body->read(8192);
                flush();
            }
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    private function buildPrivateCloudinaryDownloadUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? null;

        if (! $path) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', ltrim($path, '/'))));

        if (count($segments) < 3) {
            return null;
        }

        $cloudName = config('cloudinary.cloud_url') ? parse_url(config('cloudinary.cloud_url'))['host'] ?? null : null;
        $cloudName ??= config('cloudinary.cloud_name');

        if ($cloudName && $segments[0] === $cloudName) {
            array_shift($segments);
        }

        $resourceType = $segments[0] ?? null;
        $deliveryType = $segments[1] ?? null;
        $publicParts = array_slice($segments, 2);

        if (! empty($publicParts) && str_starts_with($publicParts[0], 'v') && ctype_digit(substr($publicParts[0], 1))) {
            array_shift($publicParts);
        }

        $publicPath = implode('/', $publicParts);
        $format = pathinfo($publicPath, PATHINFO_EXTENSION);
        $publicId = $format ? substr($publicPath, 0, -1 - strlen($format)) : $publicPath;

        if (! $resourceType || ! $publicId || ! $format) {
            return null;
        }

        try {
            return Cloudinary::uploadApi()->privateDownloadUrl($publicId, $format, [
                'type' => $deliveryType,
                'resource_type' => $resourceType,
            ]);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveFilename(?string $name, string $url, string $contentType): string
    {
        $name = $name ?: basename(parse_url($url, PHP_URL_PATH) ?: 'archivo');
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if ($extension) {
            return $name;
        }

        $extension = match ($contentType) {
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            default => '',
        };

        return $extension ? ($name . '.' . $extension) : $name;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoViewController extends Controller
{
    public function __invoke(Request $request, int $archivo): StreamedResponse
    {
        $record = Archivo::query()->findOrFail($archivo);

        if ($localPath = $this->resolveLocalPublicPath($record->ruta_storage)) {
            $contentType = $record->tipo_mime ?: Storage::disk('public')->mimeType($localPath) ?: 'application/octet-stream';
            $filename = $this->resolveFilename($record->nombre_original, $localPath, $contentType);

            return response()->stream(function () use ($localPath) {
                $stream = Storage::disk('public')->readStream($localPath);

                if (! $stream) {
                    return;
                }

                while (! feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }

                fclose($stream);
            }, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        $urls = $record->resolveRemoteFetchUrls();

        if (empty($urls)) {
            abort(404);
        }

        foreach ($urls as $url) {
            $response = Http::withOptions([
                'stream' => true,
                'http_errors' => false,
            ])->get($url);

            if (! $response->ok()) {
                continue;
            }

            $body = $response->toPsrResponse()->getBody();
            $contentType = $record->tipo_mime ?: $response->header('Content-Type') ?: 'application/octet-stream';
            $filename = $this->resolveFilename($record->nombre_original, $url, $contentType);

            return response()->stream(function () use ($body) {
                while (! $body->eof()) {
                    echo $body->read(8192);
                    flush();
                }
            }, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]);
        }

        abort(502, 'No se pudo obtener el archivo.');
    }

    /**
     * Local fallback files are stored on the public disk, sometimes as paths
     * and sometimes as /storage URLs.
     */
    private function resolveLocalPublicPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $candidate = $path;

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $candidate = parse_url($path, PHP_URL_PATH) ?: '';
        }

        $candidate = ltrim($candidate, '/');

        if (str_starts_with($candidate, 'storage/')) {
            $candidate = substr($candidate, strlen('storage/'));
        }

        return $candidate && Storage::disk('public')->exists($candidate) ? $candidate : null;
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

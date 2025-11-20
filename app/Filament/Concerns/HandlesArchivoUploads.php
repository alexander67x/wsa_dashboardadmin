<?php

namespace App\Filament\Concerns;

use App\Models\Archivo;
use App\Models\Empleado;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

trait HandlesArchivoUploads
{
    protected ?int $archivoEmpleadoFallbackId = null;

    /**
     * @var array<string, array<int, string>>
     */
    protected array $archivoUploadCache = [];

    protected function captureUploadedFiles(string $stateKey): array
    {
        return $this->archivoUploadCache[$stateKey] = $this->extractUploadedFiles($stateKey);
    }

    protected function pullUploadedFiles(string $stateKey): array
    {
        return $this->archivoUploadCache[$stateKey] ?? [];
    }

    /**
     * Obtiene los archivos cargados para una llave del formulario sin deshidratar.
     */
    protected function extractUploadedFiles(string $stateKey): array
    {
        $state = $this->form->getRawState();

        $files = $state[$stateKey] ?? [];

        if ($files instanceof Collection) {
            $files = $files->all();
        }

        if (! is_array($files)) {
            $files = [$files];
        }

        return array_values(array_filter($files, fn ($file) => is_string($file) && trim($file) !== ''));
    }

    /**
     * Guarda los archivos en la tabla archivos con subida opcional a Cloudinary.
     *
     * @param  array<int, string>  $paths
     * @param  array{
     *     entidad: string,
     *     entidad_id: int|string|null,
     *     creado_por?: int|null,
     *     folder?: string|null,
     *     disk?: string,
     *     es_foto?: bool,
     *     es_evidencia_principal?: bool
     * }  $options
     */
    protected function storeArchivos(array $paths, array $options): void
    {
        if (empty($paths)) {
            return;
        }

        $disk = $options['disk'] ?? 'public';
        $folder = $options['folder'] ?? null;
        $entidad = $options['entidad'];
        $entidadId = $options['entidad_id'];
        $creadoPor = $this->resolveCreadoPor($options['creado_por'] ?? null);
        $esFoto = $options['es_foto'] ?? false;
        $esEvidenciaPrincipal = $options['es_evidencia_principal'] ?? false;

        $storage = Storage::disk($disk);

        foreach ($paths as $path) {
            if (! $storage->exists($path)) {
                continue;
            }

            $localPath = $storage->path($path);
            $localUrl = $this->resolveLocalUrl($storage, $path);
            $mimeType = $storage->mimeType($path) ?: null;
            $fileSize = $storage->size($path) ?: null;

            $uploadData = null;
            $uploadedToCloud = false;

            if ($folder) {
                try {
                    $uploadData = Cloudinary::uploadApi()->upload($localPath, [
                        'folder' => $folder,
                        'resource_type' => 'auto',
                    ]);
                    $uploadedToCloud = true;
                } catch (Throwable $exception) {
                    Notification::make()
                        ->warning()
                        ->title('No se pudo subir el archivo a Cloudinary')
                        ->body('Se conservará el archivo en el almacenamiento local.')
                        ->send();

                    report($exception);
                }
            }

            $url = $uploadData['secure_url'] ?? $uploadData['url'] ?? $localUrl;
            $filename = $uploadData['original_filename']
                ?? basename(parse_url($url ?? $path, PHP_URL_PATH) ?: $path);
            $mime = $uploadData['resource_type'] ?? $mimeType;
            $bytes = $uploadData['bytes'] ?? $fileSize;

            if ($creadoPor === null) {
                // Sin empleado válido asignado, omitir guardado para evitar violar FK.
                continue;
            }

            Archivo::create([
                'entidad' => $entidad,
                'entidad_id' => $entidadId,
                'nombre_original' => $filename,
                'ruta_storage' => $url,
                'tipo_mime' => $mime,
                'tamano_bytes' => $bytes,
                'es_foto' => $esFoto,
                'es_evidencia_principal' => $esEvidenciaPrincipal,
                'creado_por' => $creadoPor,
            ]);

            if ($uploadedToCloud) {
                $storage->delete($path);
            }
        }
    }

    protected function resolveLocalUrl($storage, string $path): ?string
    {
        try {
            return $storage->url($path);
        } catch (Throwable) {
            return $path;
        }
    }

    protected function resolveCreadoPor(?int $creadoPor): ?int
    {
        if ($creadoPor !== null && $creadoPor > 0 && Empleado::where('cod_empleado', $creadoPor)->exists()) {
            return $creadoPor;
        }

        if ($this->archivoEmpleadoFallbackId !== null) {
            return $this->archivoEmpleadoFallbackId;
        }

        $fallback = Empleado::query()->value('cod_empleado');

        return $this->archivoEmpleadoFallbackId = $fallback ?: null;
    }
}

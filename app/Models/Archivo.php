<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Archivo extends Model
{
    protected $table = 'archivos';
    protected $primaryKey = 'id_archivo';
    public $timestamps = false;

    protected $fillable = [
        'entidad',
        'entidad_id',
        'nombre_original',
        'ruta_storage',
        'tipo_mime',
        'tamano_bytes',
        'es_foto',
        'latitud',
        'longitud',
        'tomado_en',
        'es_evidencia_principal',
        'creado_por',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'tomado_en' => 'datetime',
        'es_foto' => 'boolean',
        'es_evidencia_principal' => 'boolean',
    ];

    protected $appends = ['url'];

    public function reportes(): BelongsToMany
    {
        return $this->belongsToMany(
            ReporteAvanceTarea::class,
            'reporte_archivos',
            'archivo_id',
            'id_reporte'
        )->withPivot('es_foto_principal');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->ruta_storage) {
            return null;
        }

        // Si es una URL completa (http/https), devolverla directamente
        if (filter_var($this->ruta_storage, FILTER_VALIDATE_URL)) {
            return $this->ruta_storage;
        }

        // Si no, intentar generar la URL desde el storage local
        try {
            return Storage::url($this->ruta_storage);
        } catch (\Throwable) {
            return $this->ruta_storage;
        }
    }

    public function getDownloadUrlAttribute(): ?string
    {
        $url = $this->url;

        if (! $url) {
            return $url;
        }

        $asset = $this->parseCloudinaryAssetUrl($url);

        if (! $asset || $asset['resource_type'] !== 'raw') {
            return $url;
        }

        if ($asset['public_id'] === '' || $asset['format'] === '') {
            return $url;
        }

        try {
            return Cloudinary::uploadApi()->privateDownloadUrl($asset['public_id'], $asset['format'], [
                'type' => $asset['delivery_type'],
                'resource_type' => $asset['resource_type'],
            ]);
        } catch (\Throwable) {
            return $url;
        }
    }

    public function getDownloadUrl(): ?string
    {
        return $this->getDownloadUrlAttribute();
    }

    /**
     * Prefer signed Cloudinary download URLs first for raw assets like PDFs,
     * because direct CDN delivery may be blocked for those formats.
     *
     * @return array<int, string>
     */
    public function resolveRemoteFetchUrls(): array
    {
        $url = $this->url;

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return [];
        }

        $asset = $this->parseCloudinaryAssetUrl($url);
        $downloadUrl = $this->getDownloadUrl();

        $urls = [];

        if ($asset && $asset['resource_type'] === 'raw' && $downloadUrl && filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            $urls[] = $downloadUrl;
        }

        $urls[] = $url;

        if ($downloadUrl && filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            $urls[] = $downloadUrl;
        }

        return array_values(array_unique($urls));
    }

    /**
     * @return array{resource_type: string, delivery_type: string, public_id: string, format: string}|null
     */
    private function parseCloudinaryAssetUrl(string $url): ?array
    {
        if (! str_contains($url, 'res.cloudinary.com/')) {
            return null;
        }

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? null;

        if (! $path) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', ltrim($path, '/'))));

        if (count($segments) < 4) {
            return null;
        }

        $cloudName = $this->resolveCloudinaryCloudName();

        if ($cloudName && ($segments[0] ?? null) === $cloudName) {
            array_shift($segments);
        }

        $resourceType = $segments[0] ?? '';
        $deliveryType = $segments[1] ?? '';
        $publicParts = array_slice($segments, 2);

        if (! empty($publicParts) && str_starts_with($publicParts[0], 'v') && ctype_digit(substr($publicParts[0], 1))) {
            array_shift($publicParts);
        }

        $publicPath = rawurldecode(implode('/', $publicParts));
        $format = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
        $publicId = $format !== '' ? substr($publicPath, 0, -1 - strlen($format)) : $publicPath;

        if ($resourceType === '' || $deliveryType === '' || $publicId === '' || $format === '') {
            return null;
        }

        return [
            'resource_type' => $resourceType,
            'delivery_type' => $deliveryType,
            'public_id' => $publicId,
            'format' => $format,
        ];
    }

    private function resolveCloudinaryCloudName(): ?string
    {
        $cloudinary = Cloudinary::getFacadeRoot();
        $cloudName = $cloudinary?->configuration?->cloud?->cloudName;

        if ($cloudName) {
            return $cloudName;
        }

        $cloudUrl = config('cloudinary.cloud_url') ?? env('CLOUDINARY_URL');

        if (! $cloudUrl || ! is_string($cloudUrl)) {
            return null;
        }

        return parse_url($cloudUrl, PHP_URL_HOST) ?: null;
    }
}








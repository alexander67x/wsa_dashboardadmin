<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Api\ApiUtils;
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

        if (! $url || ! str_contains($url, 'res.cloudinary.com/')) {
            return $url;
        }

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? null;

        if (! $path) {
            return $url;
        }

        $segments = array_values(array_filter(explode('/', ltrim($path, '/'))));

        if (count($segments) < 3) {
            return $url;
        }

        $cloudNameFromUrl = $segments[0] ?? null;
        $cloudConfig = $this->resolveCloudinaryConfig();
        $cloudNameFromConfig = $cloudConfig?->cloudName;

        if ($cloudNameFromConfig && $cloudNameFromUrl === $cloudNameFromConfig) {
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

        if ($resourceType !== 'raw' || $publicId === '' || $format === '') {
            return $url;
        }

        try {
            $cloudConfig ??= $this->resolveCloudinaryConfig();

            if (! $cloudConfig) {
                return $url;
            }

            if (! in_array($resourceType, ['image', 'raw'], true)) {
                return $url;
            }

            $params = ApiUtils::finalizeUploadApiParams([
                'public_id' => $publicId,
                'format' => $format,
                'type' => $deliveryType,
                'resource_type' => $resourceType,
            ]);

            ApiUtils::signRequest($params, $cloudConfig);

            $query = ApiUtils::serializeQueryParams($params, $cloudConfig->signatureVersion ?? 2);

            return sprintf(
                'https://api.cloudinary.com/v1_1/%s/%s/download?%s',
                $cloudConfig->cloudName,
                $resourceType,
                $query
            );
        } catch (\Throwable) {
            return $url;
        }

        return $url;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->getDownloadUrlAttribute();
    }

    private function resolveCloudinaryConfig(): ?object
    {
        $cloudinary = Cloudinary::getFacadeRoot();
        $cloudConfig = $cloudinary?->configuration?->cloud;

        if ($cloudConfig && $cloudConfig->cloudName && $cloudConfig->apiKey && $cloudConfig->apiSecret) {
            return $cloudConfig;
        }

        $cloudUrl = config('cloudinary.cloud_url') ?? env('CLOUDINARY_URL');

        if (! $cloudUrl || ! is_string($cloudUrl)) {
            return null;
        }

        $parsed = parse_url($cloudUrl);

        $cloudName = $parsed['host'] ?? null;
        $apiKey = $parsed['user'] ?? null;
        $apiSecret = $parsed['pass'] ?? null;

        if (! $cloudName || ! $apiKey || ! $apiSecret) {
            return null;
        }

        $config = new \stdClass();
        $config->cloudName = $cloudName;
        $config->apiKey = $apiKey;
        $config->apiSecret = $apiSecret;
        $config->signatureVersion = 2;

        return $config;
    }
}









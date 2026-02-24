<?php

namespace Rapidez\StatamicQueryBuilder\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class DefaultQueryService
{
    protected const STORAGE_PATH = 'rapidez/default-query.yaml';

    public function getDefaultEnabled(): bool
    {
        $settings = $this->getDefaultQuerySettings();

        return $settings['enabled'];
    }

    public function getDefaultQuery(): array
    {
        $settings = $this->getDefaultQuerySettings();

        return $settings['query'];
    }

    public function getDefaultQuerySettings(): array
    {
        $configEnabled = (bool) (config('rapidez.query-builder.default_query.enabled') ?? true);
        $configQuery = config('rapidez.query-builder.default_query.query') ?? [];

        if (! is_array($configQuery)) {
            $configQuery = [];
        }

        $fallback = [
            'enabled' => $configEnabled,
            'query' => $configQuery,
        ];

        if (! Storage::disk('local')->exists(self::STORAGE_PATH)) {
            return $fallback;
        }

        try {
            $content = Storage::disk('local')->get(self::STORAGE_PATH);
            $parsed = Yaml::parse($content);

            if (! is_array($parsed)) {
                return $fallback;
            }

            $enabled = isset($parsed['enabled']) ? (bool) $parsed['enabled'] : $configEnabled;
            $query = isset($parsed['query']) && is_array($parsed['query']) ? $parsed['query'] : $configQuery;

            return [
                'enabled' => $enabled,
                'query' => $query,
            ];
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public function saveDefaultQuerySettings(array $payload): void
    {
        $configEnabled = (bool) (config('rapidez.query-builder.default_query.enabled') ?? true);
        $configQuery = config('rapidez.query-builder.default_query.query') ?? [];

        if (! is_array($configQuery)) {
            $configQuery = [];
        }

        $enabled = isset($payload['enabled']) ? (bool) $payload['enabled'] : $configEnabled;
        $query = isset($payload['query']) && is_array($payload['query']) ? $payload['query'] : $configQuery;

        $data = [
            'enabled' => $enabled,
            'query' => $query,
        ];

        $yaml = Yaml::dump($data, 4, 2);

        Storage::disk('local')->put(self::STORAGE_PATH, $yaml);
    }

    public function getStoragePath(): string
    {
        return storage_path('app/'.self::STORAGE_PATH);
    }
}

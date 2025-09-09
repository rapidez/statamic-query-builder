<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class QueryPresetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $presetFiles = config('query-builder.preset_files', []);
            $categorizedPresets = [];

            foreach ($presetFiles as $filePath) {
                $fullPath = base_path($filePath);

                if (!File::exists($fullPath)) {
                    Log::warning("Query preset file not found: {$fullPath}");
                    continue;
                }

                $content = File::get($fullPath);
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("Invalid JSON in preset file: {$filePath} - " . json_last_error_msg());
                    continue;
                }

                if (!$this->isValidPresetStructure($data)) {
                    Log::error("Invalid preset structure in file: {$filePath}");
                    continue;
                }

                $categoryKey = $data['category']['key'];

                if (!isset($categorizedPresets[$categoryKey])) {
                    $categorizedPresets[$categoryKey] = [
                        'label' => $data['category']['label'],
                        'presets' => []
                    ];
                }

                foreach ($data['presets'] as $preset) {
                    $categorizedPresets[$categoryKey]['presets'][] = [
                        'key' => $preset['key'],
                        'name' => $preset['name'],
                        'description' => $preset['description'] ?? '',
                        'query' => $preset['query']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'categories' => $categorizedPresets
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading query presets: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load query presets'
            ], 500);
        }
    }

    private function isValidPresetStructure(array $data): bool
    {
        if (!isset($data['category']) || !isset($data['presets'])) {
            return false;
        }

        if (!isset($data['category']['key']) || !isset($data['category']['label'])) {
            return false;
        }

        if (!is_array($data['presets'])) {
            return false;
        }

        foreach ($data['presets'] as $preset) {
            if (!isset($preset['key']) || !isset($preset['name']) || !isset($preset['query'])) {
                return false;
            }

            if (!isset($preset['query']['groups']) || !isset($preset['query']['globalConjunction'])) {
                return false;
            }
        }

        return true;
    }
}

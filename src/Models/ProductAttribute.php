<?php

namespace Rapidez\StatamicQueryBuilder\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Rapidez\Core\Models\Attribute as CoreAttribute;
use Rapidez\StatamicQueryBuilder\Observers\ProductAttributeObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

#[ObservedBy(ProductAttributeObserver::class)]
class ProductAttribute extends CoreAttribute
{
    use HasRunwayResource;

    protected $appends = [
        'options',
        'frontend_label',
        'formatted_options',
    ];

    public static $inputTypes = [
        'text' => 'Text Field',
        'textarea' => 'Text Area',
        'date' => 'Date',
        'boolean' => 'Yes/No',
        'multiselect' => 'Multiple Select',
        'select' => 'Dropdown',
        'price' => 'Price',
        'media_image' => 'Media Image',
        'weee' => 'Fixed Product Tax',
        'swatch_visual' => 'Visual Swatch',
        'swatch_text' => 'Text Swatch',
    ];

    protected const ATTRIBUTE_MAP = [
        'attribute_id' => 'id',
        'frontend_label' => 'name',
        'attribute_code' => 'code',
        'backend_type' => 'type',
        'frontend_input' => 'input',
        'is_searchable' => 'search',
        'is_filterable' => 'filter',
        'is_comparable' => 'compare',
        'used_in_product_listing' => 'listing',
        'used_for_sort_by' => 'sorting',
        'is_visible_on_front' => 'productpage',
        'is_html_allowed_on_front' => 'html',
    ];

    protected const BOOLEAN_FIELDS = [
        'search', 'filter', 'compare', 'listing', 'sorting',
        'productpage', 'html', 'flat', 'super', 'text_swatch',
        'visual_swatch', 'update_image', 'is_required', 'is_comparable',
        'used_in_product_listing', 'used_for_sort_by',
        'is_visible_on_front', 'is_html_allowed_on_front',
    ];

    protected static function booting(): void
    {
        parent::booting();

        static::addGlobalScope('add_missing_columns', function ($builder) {
            $builder->addSelect([
                'eav_attribute.attribute_id as attribute_id_qualified',
                'catalog_eav_attribute.is_comparable',
                'catalog_eav_attribute.used_in_product_listing',
                'catalog_eav_attribute.used_for_sort_by',
                'catalog_eav_attribute.is_visible_on_front',
                'catalog_eav_attribute.is_html_allowed_on_front',
                'eav_attribute.is_required',
            ]);
        });
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder
        {
            public function orderBy($column, $direction = 'asc')
            {
                if ($column === 'attribute_id') {
                    $column = 'eav_attribute.attribute_id';
                }

                return parent::orderBy($column, $direction);
            }
        };
    }

    public function getQualifiedKeyName(): string
    {
        return 'eav_attribute.attribute_id';
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function getAttribute($key)
    {
        if (isset(self::ATTRIBUTE_MAP[$key])) {
            $value = parent::getAttribute(self::ATTRIBUTE_MAP[$key]);
            if ($value === null && $key !== self::ATTRIBUTE_MAP[$key]) {
                $value = parent::getAttribute($key);
            }
        } else {
            $value = parent::getAttribute($key);
        }

        if (in_array($key, self::BOOLEAN_FIELDS) && $value !== null) {
            return (bool) $value;
        }

        return $value;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (isset($array['id']) && ! isset($array['attribute_id'])) {
            $array['attribute_id'] = $array['id'];
        }
        if (isset($array['name']) && ! isset($array['frontend_label'])) {
            $array['frontend_label'] = $array['name'];
        }

        foreach (self::BOOLEAN_FIELDS as $field) {
            if (isset($array[$field])) {
                $array[$field] = (bool) $array[$field];
            }
        }

        return $array;
    }

    public function getCacheKey(): string
    {
        return "product_attribute_{$this->attribute_id}_store_".config('rapidez.store');
    }

    public function frontendLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->store_frontend_label ?? $this->attributes['name'] ?? ''
        );
    }

    public function options(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! isset($this->attributes['option_ids']) || ! isset($this->attributes['option_values'])) {
                    $this->loadOptions();
                }

                $optionIds = $this->attributes['option_ids'] ?? '';
                $optionValues = $this->attributes['option_values'] ?? '';

                return collect([$optionIds, $optionValues])
                    ->map(fn ($value) => collect(explode(',', $value))->filter())
                    ->pipe(function (Collection $collections) {
                        $optionIds = $collections[0];
                        $optionValues = $collections[1];

                        if ($optionIds->count() === $optionValues->count()) {
                            return $optionIds->combine($optionValues);
                        }

                        return [];
                    });
            }
        );
    }

    protected function loadOptions(): void
    {
        $options = \Illuminate\Support\Facades\DB::table('eav_attribute_option')
            ->join('eav_attribute_option_value', 'eav_attribute_option.option_id', '=', 'eav_attribute_option_value.option_id')
            ->where('eav_attribute_option.attribute_id', $this->getAttribute('id'))
            ->where('eav_attribute_option_value.store_id', 0)
            ->orderBy('eav_attribute_option.sort_order')
            ->select('eav_attribute_option.option_id', 'eav_attribute_option_value.value')
            ->get();

        $this->attributes['option_ids'] = $options->pluck('option_id')->join(',');
        $this->attributes['option_values'] = $options->pluck('value')->join(',');
    }

    public function formattedOptions(): Attribute
    {
        return Attribute::make(
            get: fn () => empty($this->options) ? '' :
                collect($this->options)
                    ->map(fn ($value, $id) => "{$value} (ID: {$id})")
                    ->join(', ')
        );
    }

    public function attributeOptions(): HasMany
    {
        return $this->hasMany(ProductAttributeOption::class, 'attribute_id', 'attribute_id');
    }

    public function scopeRunwaySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->where('eav_attribute.attribute_id', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute.attribute_code', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute.frontend_label', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute.frontend_input', 'LIKE', "%{$search}%");
        });
    }
}

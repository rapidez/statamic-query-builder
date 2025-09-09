<?php

namespace Rapidez\StatamicQueryBuilder\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rapidez\StatamicQueryBuilder\Observers\ProductAttributeObserver;
use Statamic\Facades\Site;
use Statamic\Statamic;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

#[ObservedBy(ProductAttributeObserver::class)]
class ProductAttributeOption extends Model
{
    use HasRunwayResource;

    protected $table = 'eav_attribute_option';

    protected $primaryKey = 'option_id';

    public $timestamps = false;

    protected $appends = [
        'admin_value',
        'store_value',
    ];

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->join('eav_attribute', 'eav_attribute.attribute_id', '=', 'eav_attribute_option.attribute_id')
                ->where('eav_attribute.entity_type_id', function ($query) {
                    $query->select('entity_type_id')
                        ->from('eav_entity_type')
                        ->where('entity_type_code', 'catalog_product');
                });

            $builder->leftJoin('eav_attribute_option_value as admin_value', function ($join) {
                $join->on('eav_attribute_option.option_id', '=', 'admin_value.option_id')
                    ->where('admin_value.store_id', 0);
            });

            $builder->leftJoin('eav_attribute_option_value as store_value', function ($join) {
                $join->on('eav_attribute_option.option_id', '=', 'store_value.option_id')
                    ->where('store_value.store_id', self::getCurrentStoreId());
            });

            $builder->select([
                'eav_attribute_option.option_id',
                'eav_attribute_option.attribute_id',
                'eav_attribute_option.sort_order',
                'eav_attribute.attribute_code',
                'eav_attribute.frontend_label as attribute_label',
                'admin_value.value as admin_value',
                'store_value.value as store_value',
            ]);
        });
    }

    protected static function getCurrentStoreId(): string
    {
        return once(fn () => (Statamic::isCpRoute()
            ? (Site::selected()->attributes['magento_store_id'] ?? '1')
            : (Site::current()->attributes['magento_store_id'] ?? '1')
        ));
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id', 'attribute_id');
    }

    public function adminValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['admin_value'] ?? ''
        );
    }

    public function storeValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributes['store_value'] ?? $this->admin_value
        );
    }

    public function displayValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->store_value ?: $this->admin_value
        );
    }

    public function getCacheKey(): string
    {
        return "product_attribute_option_{$this->option_id}_store_".self::getCurrentStoreId();
    }

    public function scopeRunwaySearch(Builder $query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('eav_attribute_option.option_id', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute.attribute_code', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute.frontend_label', 'LIKE', "%{$search}%")
                ->orWhere('admin_value.value', 'LIKE', "%{$search}%")
                ->orWhere('store_value.value', 'LIKE', "%{$search}%")
                ->orWhere('eav_attribute_option.sort_order', 'LIKE', "%{$search}%");
        });
    }
}

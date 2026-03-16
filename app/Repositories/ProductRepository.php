<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * Cached custom attribute IDs used for normalized search.
     *
     * @var array<string, int|null>
     */
    protected array $measurementAttributeIds = [];

    /**
     * Search product from database.
     */
    public function searchFromDatabase(array $params = [])
    {
        $params['url_key'] ??= null;

        if (! empty($params['query'])) {
            $params['name'] = $params['query'];
        }

        $measurements = $this->parseMeasurementQuery($params['query'] ?? null);

        $query = $this->with([
            'attribute_family',
            'images',
            'videos',
            'categories',
            'attribute_values',
            'price_indices',
            'inventory_indices',
            'reviews',
            'variants',
            'variants.attribute_family',
            'variants.attribute_values',
            'variants.price_indices',
            'variants.inventory_indices',
        ])->scopeQuery(function ($query) use ($params, $measurements) {
            $prefix = DB::getTablePrefix();

            $qb = $query->distinct()
                ->select('products.*')
                ->leftJoin('products as variants', DB::raw('COALESCE('.$prefix.'variants.parent_id, '.$prefix.'variants.id)'), '=', 'products.id')
                ->leftJoin('product_price_indices', function ($join) {
                    $customerGroup = $this->customerRepository->getCurrentGroup();

                    $join->on('products.id', '=', 'product_price_indices.product_id')
                        ->where('product_price_indices.customer_group_id', $customerGroup->id);
                });

            if (! empty($params['category_id'])) {
                $qb->leftJoin('product_categories', 'product_categories.product_id', '=', 'products.id')
                    ->whereIn('product_categories.category_id', explode(',', $params['category_id']));
            }

            if (! empty($params['channel_id'])) {
                $qb->leftJoin('product_channels', 'products.id', '=', 'product_channels.product_id')
                    ->where('product_channels.channel_id', explode(',', $params['channel_id']));
            }

            if (! empty($params['type'])) {
                $qb->where('products.type', $params['type']);

                if (
                    $params['type'] === 'simple'
                    && ! empty($params['exclude_customizable_products'])
                ) {
                    $qb->leftJoin('product_customizable_options', 'products.id', '=', 'product_customizable_options.product_id')
                        ->whereNull('product_customizable_options.id');
                }
            }

            if (! empty($params['price'])) {
                $priceRange = explode(',', $params['price']);

                $qb->whereBetween('product_price_indices.min_price', [
                    core()->convertToBasePrice(current($priceRange)),
                    core()->convertToBasePrice(end($priceRange)),
                ]);
            }

            foreach (['contenance_cl', 'degre_alcool'] as $rangeAttributeCode) {
                if (empty($params[$rangeAttributeCode])) {
                    continue;
                }

                $rangeAttribute = $this->attributeRepository->findOneByField('code', $rangeAttributeCode);

                if (! $rangeAttribute) {
                    continue;
                }

                $rangeAlias = $rangeAttributeCode.'_range_product_attribute_values';
                $rangeValues = array_map('floatval', explode(',', $params[$rangeAttributeCode]));

                $rangeMin = (float) ($rangeValues[0] ?? 0);
                $rangeMax = (float) ($rangeValues[1] ?? $rangeMin);

                $qb->leftJoin('product_attribute_values as '.$rangeAlias, function ($join) use ($rangeAlias, $rangeAttribute) {
                    $join->on('products.id', '=', $rangeAlias.'.product_id')
                        ->where($rangeAlias.'.attribute_id', $rangeAttribute->id);
                })->whereBetween($rangeAlias.'.'.$rangeAttribute->column_name, [$rangeMin, $rangeMax]);
            }

            $filterableAttributes = $this->attributeRepository->getProductDefaultAttributes(array_keys($params));

            $attributes = $filterableAttributes->whereIn('code', [
                'name',
                'status',
                'visible_individually',
                'url_key',
            ]);

            foreach ($attributes as $attribute) {
                $alias = $attribute->code.'_product_attribute_values';

                $qb->leftJoin('product_attribute_values as '.$alias, 'products.id', '=', $alias.'.product_id')
                    ->where($alias.'.attribute_id', $attribute->id);

                if ($attribute->code == 'name') {
                    $decodedQuery = urldecode($params['name']);
                    $synonyms = $this->searchSynonymRepository->getSynonymsByQuery($decodedQuery);

                    $qb->where(function ($subQuery) use ($alias, $synonyms, $measurements) {
                        foreach ($synonyms as $synonym) {
                            $subQuery->orWhere($alias.'.text_value', 'like', '%'.$synonym.'%');
                        }

                        $this->appendMeasurementSearchClauses($subQuery, $measurements);
                    });
                } elseif ($attribute->code == 'url_key') {
                    if (empty($params['url_key'])) {
                        $qb->whereNotNull($alias.'.text_value');
                    } else {
                        $qb->where($alias.'.text_value', 'like', '%'.urldecode($params['url_key']).'%');
                    }
                } else {
                    if (is_null($params[$attribute->code])) {
                        continue;
                    }

                    $qb->where($alias.'.'.$attribute->column_name, 1);
                }
            }

            $attributes = $filterableAttributes->whereNotIn('code', [
                'price',
                'contenance_cl',
                'degre_alcool',
                'name',
                'status',
                'visible_individually',
                'url_key',
            ]);

            if ($attributes->isNotEmpty()) {
                $qb->where(function ($filterQuery) use ($qb, $params, $attributes, $prefix) {
                    $aliases = [
                        'products' => 'product_attribute_values',
                        'variants' => 'variant_attribute_values',
                    ];

                    foreach ($aliases as $table => $tableAlias) {
                        $filterQuery->orWhere(function ($subFilterQuery) use ($qb, $params, $attributes, $prefix, $table, $tableAlias) {
                            foreach ($attributes as $attribute) {
                                $alias = $attribute->code.'_'.$tableAlias;

                                $qb->leftJoin('product_attribute_values as '.$alias, function ($join) use ($table, $alias, $attribute) {
                                    $join->on($table.'.id', '=', $alias.'.product_id');

                                    $join->where($alias.'.attribute_id', $attribute->id);
                                });

                                if (in_array($attribute->type, [
                                    AttributeTypeEnum::CHECKBOX->value,
                                    AttributeTypeEnum::MULTISELECT->value,
                                ])) {
                                    $paramValues = explode(',', $params[$attribute->code]);

                                    $subFilterQuery->where(function ($query) use ($paramValues, $alias, $attribute, $prefix) {
                                        foreach ($paramValues as $value) {
                                            $query->orWhereRaw("FIND_IN_SET(?, {$prefix}{$alias}.{$attribute->column_name})", [$value]);
                                        }
                                    });
                                } else {
                                    $subFilterQuery->whereIn($alias.'.'.$attribute->column_name, explode(',', $params[$attribute->code]));
                                }
                            }
                        });
                    }
                });

                $qb->groupBy('products.id');
            }

            $sortOptions = $this->getSortOptions($params);

            if ($sortOptions['order'] != 'rand') {
                $attribute = $this->attributeRepository->findOneByField('code', $sortOptions['sort']);

                if ($attribute) {
                    if ($attribute->code === 'price') {
                        $qb->orderBy('product_price_indices.min_price', $sortOptions['order']);
                    } else {
                        $alias = 'sort_product_attribute_values';

                        $qb->leftJoin('product_attribute_values as '.$alias, function ($join) use ($alias, $attribute) {
                            $join->on('products.id', '=', $alias.'.product_id')
                                ->where($alias.'.attribute_id', $attribute->id);

                            if ($attribute->value_per_channel) {
                                if ($attribute->value_per_locale) {
                                    $join->where($alias.'.channel', core()->getRequestedChannelCode())
                                        ->where($alias.'.locale', core()->getRequestedLocaleCode());
                                } else {
                                    $join->where($alias.'.channel', core()->getRequestedChannelCode());
                                }
                            } else {
                                if ($attribute->value_per_locale) {
                                    $join->where($alias.'.locale', core()->getRequestedLocaleCode());
                                }
                            }
                        })
                            ->orderBy($alias.'.'.$attribute->column_name, $sortOptions['order']);
                    }
                } else {
                    $qb->orderBy('products.created_at', $sortOptions['order']);
                }
            } else {
                return $qb->inRandomOrder();
            }

            return $qb->groupBy('products.id');
        });

        $limit = $this->getPerPageLimit($params);

        return $query->paginate($limit);
    }

    /**
     * Search product from elastic search.
     */
    public function searchFromElastic(array $params = [])
    {
        $currentPage = Paginator::resolveCurrentPage('page');

        $limit = $this->getPerPageLimit($params);

        $sortOptions = $this->getSortOptions($params);

        $indices = $this->elasticSearchRepository->search($params, [
            'from' => ($currentPage * $limit) - $limit,
            'limit' => $limit,
            'sort' => $sortOptions['sort'],
            'order' => $sortOptions['order'],
        ]);

        $query = $this->with([
            'attribute_family',
            'images',
            'videos',
            'attribute_values',
            'price_indices',
            'inventory_indices',
            'reviews',
            'variants',
            'variants.attribute_family',
            'variants.attribute_values',
            'variants.price_indices',
            'variants.inventory_indices',
        ])->scopeQuery(function ($query) use ($params, $indices) {
            $qb = $query->distinct()
                ->whereIn('products.id', $indices['ids']);

            if (
                ! empty($params['type'])
                && $params['type'] === 'simple'
                && ! empty($params['exclude_customizable_products'])
            ) {
                $qb->leftJoin('product_customizable_options', 'products.id', '=', 'product_customizable_options.product_id')
                    ->whereNull('product_customizable_options.id');
            }

            $qb->orderBy(DB::raw('FIELD(id, '.implode(',', $indices['ids']).')'));

            return $qb;
        });

        $items = $indices['total'] ? $query->get() : [];

        return new LengthAwarePaginator($items, $indices['total'], $limit, $currentPage, [
            'path' => request()->url(),
            'query' => $params,
        ]);
    }

    /**
     * Parse a free-text query and detect numeric intents for contenance/degre.
     *
     * @return array{contenance: float|null, degre: float|null}
     */
    protected function parseMeasurementQuery(?string $query): array
    {
        $query = trim((string) $query);

        if ($query === '') {
            return ['contenance' => null, 'degre' => null];
        }

        if (! preg_match('/(\d+(?:[\.,]\d+)?)/u', $query, $matches)) {
            return ['contenance' => null, 'degre' => null];
        }

        $value = (float) str_replace(',', '.', $matches[1]);
        $compact = preg_replace('/\s+/u', '', mb_strtolower($query));

        $hasPercent = str_contains($compact, '%');
        $hasCl = str_contains($compact, 'cl');
        $isOnlyNumber = (bool) preg_match('/^\d+(?:[\.,]\d+)?$/u', str_replace(' ', '', $query));

        if ($hasPercent) {
            return ['contenance' => null, 'degre' => $value];
        }

        if ($hasCl) {
            return ['contenance' => $value, 'degre' => null];
        }

        if ($isOnlyNumber) {
            return ['contenance' => $value, 'degre' => $value];
        }

        return ['contenance' => null, 'degre' => null];
    }

    /**
     * Add OR clauses for normalized measurement search.
     */
    protected function appendMeasurementSearchClauses($subQuery, array $measurements): void
    {
        if (! is_numeric($measurements['contenance'])) {
            $measurements['contenance'] = null;
        }

        if (! is_numeric($measurements['degre'])) {
            $measurements['degre'] = null;
        }

        if (
            is_null($measurements['contenance'])
            && is_null($measurements['degre'])
        ) {
            return;
        }

        if (! is_null($measurements['contenance'])) {
            $contenanceAttributeId = $this->getMeasurementAttributeId('contenance_cl');

            if ($contenanceAttributeId) {
                $value = (float) $measurements['contenance'];

                $subQuery->orWhereExists(function ($query) use ($contenanceAttributeId, $value) {
                    $query->select(DB::raw(1))
                        ->from('product_attribute_values as measurement_contenance')
                        ->whereColumn('measurement_contenance.product_id', 'products.id')
                        ->where('measurement_contenance.attribute_id', $contenanceAttributeId)
                        ->whereBetween('measurement_contenance.float_value', [$value - 0.001, $value + 0.001]);
                });
            }
        }

        if (! is_null($measurements['degre'])) {
            $degreAttributeId = $this->getMeasurementAttributeId('degre_alcool');

            if ($degreAttributeId) {
                $value = (float) $measurements['degre'];

                $subQuery->orWhereExists(function ($query) use ($degreAttributeId, $value) {
                    $query->select(DB::raw(1))
                        ->from('product_attribute_values as measurement_degre')
                        ->whereColumn('measurement_degre.product_id', 'products.id')
                        ->where('measurement_degre.attribute_id', $degreAttributeId)
                        ->whereBetween('measurement_degre.float_value', [$value - 0.001, $value + 0.001]);
                });
            }
        }
    }

    /**
     * Resolve and cache a measurement attribute id by code.
     */
    protected function getMeasurementAttributeId(string $code): ?int
    {
        if (array_key_exists($code, $this->measurementAttributeIds)) {
            return $this->measurementAttributeIds[$code];
        }

        $attribute = $this->attributeRepository->findOneByField('code', $code);

        $this->measurementAttributeIds[$code] = $attribute?->id;

        return $this->measurementAttributeIds[$code];
    }
}

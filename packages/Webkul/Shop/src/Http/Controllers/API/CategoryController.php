<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Resources\AttributeOptionResource;
use Webkul\Shop\Http\Resources\AttributeResource;
use Webkul\Shop\Http\Resources\CategoryResource;
use Webkul\Shop\Http\Resources\CategoryTreeResource;

class CategoryController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get all categories.
     */
    public function index(): JsonResource
    {
        /**
         * These are the default parameters. By default, only the enabled category
         * will be shown in the current locale.
         */
        $defaultParams = [
            'status' => 1,
            'locale' => app()->getLocale(),
        ];

        $categories = $this->categoryRepository->getAll(array_merge($defaultParams, request()->all()));

        return CategoryResource::collection($categories);
    }

    /**
     * Get all categories in tree format.
     */
    public function tree(): JsonResource
    {
        $categories = $this->categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id);

        return CategoryTreeResource::collection($categories);
    }

    /**
     * Get filterable attributes for category.
     */
    public function getAttributes(): JsonResource
    {
        if (! request('category_id')) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();

            return AttributeResource::collection($filterableAttributes);
        }

        $category = $this->categoryRepository->findOrFail(request('category_id'));

        if (empty($filterableAttributes = $category->filterableAttributes)) {
            $filterableAttributes = $this->attributeRepository->getFilterableAttributes();
        }

        return AttributeResource::collection($filterableAttributes);
    }

    /**
     * Get attribute options with pagination and search.
     */
    public function getAttributeOptions(int $attributeId): mixed
    {
        $attribute = $this->attributeRepository->findOrFail($attributeId);

        if ($attribute->type === AttributeTypeEnum::BOOLEAN->value) {
            return new JsonResponse([
                'data' => AttributeTypeEnum::getBooleanOptions(),
            ]);
        }

        $query = $attribute->options()
            ->with([
                'translation' => fn ($query) => $query->where('locale', core()->getCurrentLocale()->code),
            ]);

        if ($search = request('search')) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('translation', fn ($query) => $query->where('label', 'like', "%{$search}%"))
                    ->orWhere('admin_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy('sort_order');

        return AttributeOptionResource::collection($query->paginate());
    }

    /**
     * Get product maximum price.
     */
    public function getProductMaxPrice($categoryId = null): JsonResource
    {
        if (core()->getConfigData('catalog.products.search.engine') == 'elastic') {
            $searchEngine = core()->getConfigData('catalog.products.search.storefront_mode');
        }

        $maxPrice = $this->productRepository
            ->setSearchEngine($searchEngine ?? 'database')
            ->getMaxPrice(['category_id' => $categoryId]);

        return new JsonResource([
            'max_price' => core()->convertPrice($maxPrice),
        ]);
    }

    /**
     * Get maximum value for a numeric attribute within an optional category.
     */
    public function getAttributeMaxValue(int $attributeId, $categoryId = null): JsonResource
    {
        $attribute = $this->attributeRepository->findOrFail($attributeId);

        $column = $attribute->column_name;

        $query = DB::table('product_attribute_values')
            ->join('products', 'products.id', '=', 'product_attribute_values.product_id')
            ->join('product_channels', 'products.id', '=', 'product_channels.product_id')
            ->where('product_attribute_values.attribute_id', $attribute->id)
            ->where('product_channels.channel_id', core()->getCurrentChannel()->id)
            ->whereNotNull('product_attribute_values.'.$column);

        if ($categoryId) {
            $query->join('product_categories', 'products.id', '=', 'product_categories.product_id')
                ->where('product_categories.category_id', $categoryId);
        }

        $maxValue = (float) ($query->max('product_attribute_values.'.$column) ?? 0);

        return new JsonResource([
            'max_value' => $maxValue,
        ]);
    }
}

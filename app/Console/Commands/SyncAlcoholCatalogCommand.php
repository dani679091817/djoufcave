<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeFamily;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;

class SyncAlcoholCatalogCommand extends Command
{
    protected $signature = 'catalog:sync-alcohol-products
                            {--file=Articles_VIN_WHI_enriched.xlsx : Excel file path relative to project root}
                            {--dry-run : Parse and validate rows without writing products}';

    protected $description = 'Sync alcohol categories, attributes, family, and import products from the source Excel file.';

    /**
     * Managed category paths from business root.
     *
     * @var array<int, string>
     */
    protected array $managedCategoryPaths = [
        'Boissons alcoolisees',
        'Boissons alcoolisees > Vins',
        'Boissons alcoolisees > Vins > Vin rouge',
        'Boissons alcoolisees > Vins > Vin blanc',
        'Boissons alcoolisees > Vins > Vin rose',
        'Boissons alcoolisees > Vins > Vin mousseux',
        'Boissons alcoolisees > Spiritueux',
        'Boissons alcoolisees > Spiritueux > Whisky',
        'Boissons alcoolisees > Spiritueux > Rhum',
        'Boissons alcoolisees > Spiritueux > Vodka',
        'Boissons alcoolisees > Spiritueux > Gin',
        'Boissons alcoolisees > Spiritueux > Tequila',
        'Boissons alcoolisees > Spiritueux > Brandy',
        'Boissons alcoolisees > Liqueurs',
        'Boissons alcoolisees > Bieres',
        'Boissons alcoolisees > Cidres',
    ];

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected AttributeRepository $attributeRepository,
        protected ProductRepository $productRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));
        $dryRun = (bool) $this->option('dry-run');

        if (! file_exists($filePath)) {
            $this->error("Fichier introuvable: {$filePath}");

            return self::FAILURE;
        }

        $this->info('Synchronisation des categories/attributs/famille...');

        [$categoryPathToId, $managedCategoryIds] = $this->ensureCategoryTree();
        [$contenanceAttribute, $degreAttribute] = $this->ensureBusinessAttributes();
        $family = $this->ensureAttributeFamily($contenanceAttribute, $degreAttribute);

        $this->attachFilterableAttributesToManagedCategories([
            $contenanceAttribute->id,
            $degreAttribute->id,
        ], $managedCategoryIds);

        $this->line('Import des produits depuis Excel...');

        [$rows, $headerMap] = $this->readRows($filePath);

        $requiredHeaders = [
            'reference',
            'nom',
            'designation',
            'contenance_cl',
            'categorie',
            'degre_alcool',
            'prix_ttc',
        ];

        foreach ($requiredHeaders as $requiredHeader) {
            if (! array_key_exists($requiredHeader, $headerMap)) {
                $this->error("Colonne obligatoire absente dans Excel: {$requiredHeader}");

                return self::FAILURE;
            }
        }

        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($rows as $rowNumber => $row) {
            $sku = trim((string) ($row[$headerMap['reference']] ?? ''));
            $name = trim((string) ($row[$headerMap['nom']] ?? ''));
            $designation = trim((string) ($row[$headerMap['designation']] ?? ''));

            if ($sku === '' || ($name === '' && $designation === '')) {
                $stats['skipped']++;

                continue;
            }

            $name = $name !== '' ? $name : $designation;
            $description = $designation !== '' ? $designation : $name;

            $contenance = $this->parseDecimal($row[$headerMap['contenance_cl']] ?? null);
            $degre = $this->parseDecimal($row[$headerMap['degre_alcool']] ?? null, stripPercent: true);
            $price = $this->parsePrice($row[$headerMap['prix_ttc']] ?? null);

            if (is_null($price)) {
                $stats['skipped']++;
                $this->warn("Ligne {$rowNumber}: prix invalide, produit ignore ({$sku})");

                continue;
            }

            $excelCategoryPath = trim((string) ($row[$headerMap['categorie']] ?? ''));
            $businessCategoryPath = $this->mapExcelPathToBusinessPath($excelCategoryPath);

            if (! $businessCategoryPath) {
                $stats['skipped']++;
                $this->warn("Ligne {$rowNumber}: categorie inconnue '{$excelCategoryPath}', produit ignore ({$sku})");

                continue;
            }

            $categoryId = $categoryPathToId[$businessCategoryPath] ?? null;

            if (! $categoryId) {
                $categoryId = $this->ensureBusinessPathCategory($businessCategoryPath);
                $categoryPathToId[$businessCategoryPath] = $categoryId;
            }

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use (
                $sku,
                $name,
                $description,
                $price,
                $contenance,
                $degre,
                $categoryId,
                $family,
                &$stats
            ) {
                $existingProduct = Product::query()->where('sku', $sku)->first();

                if (! $existingProduct) {
                    $existingProduct = $this->productRepository->create([
                        'type' => 'simple',
                        'attribute_family_id' => $family->id,
                        'sku' => $sku,
                    ]);

                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }

                $urlKey = $existingProduct->url_key ?: Str::slug($name.'-'.$sku);

                if ($urlKey === '') {
                    $urlKey = Str::slug($sku);
                }

                $this->productRepository->update([
                    'sku' => $sku,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'status' => 1,
                    'visible_individually' => 1,
                    'manage_stock' => 0,
                    'url_key' => $urlKey,
                    'channel' => core()->getDefaultChannelCode(),
                    'locale' => core()->getDefaultLocaleCodeFromDefaultChannel(),
                    'categories' => [$categoryId],
                    'contenance_cl' => $contenance,
                    'degre_alcool' => $degre,
                ], $existingProduct->id);
            });
        }

        if ($dryRun) {
            $this->info('Mode dry-run actif: aucun produit ecrit.');
        } else {
            $this->line('Reindexation produit (price, flat)...');
            Artisan::call('indexer:index', [
                '--type' => ['price', 'flat'],
                '--mode' => ['full'],
            ]);
            $this->line(Artisan::output());
        }

        $this->table(['created', 'updated', 'skipped'], [[$stats['created'], $stats['updated'], $stats['skipped']]]);

        return self::SUCCESS;
    }

    /**
     * @return array{0: array<string, int>, 1: array<int, int>}
     */
    protected function ensureCategoryTree(): array
    {
        $rootCategoryId = core()->getDefaultChannel()->root_category_id;

        $categoryPathToId = [];
        $managedCategoryIds = [];

        foreach ($this->managedCategoryPaths as $businessPath) {
            $segments = array_map('trim', explode('>', $businessPath));
            $parentId = $rootCategoryId;
            $resolved = [];

            foreach ($segments as $segment) {
                $name = $this->displayCategoryName($segment);
                $category = $this->findCategoryByParentAndName($parentId, $name);

                if (! $category) {
                    $category = $this->categoryRepository->create([
                        'locale' => 'all',
                        'name' => $name,
                        'slug' => $this->makeUniqueSlug($name),
                        'description' => $name,
                        'status' => 1,
                        'display_mode' => 'products_only',
                        'parent_id' => $parentId,
                    ]);
                }

                $parentId = $category->id;
                $resolved[] = $this->normalizeLabel($name);
                $managedCategoryIds[$category->id] = $category->id;
                $categoryPathToId[implode(' > ', $resolved)] = $category->id;
            }
        }

        return [$categoryPathToId, array_values($managedCategoryIds)];
    }

    /**
     * @return array{0: Attribute, 1: Attribute}
     */
    protected function ensureBusinessAttributes(): array
    {
        $contenance = $this->attributeRepository->findOneByField('code', 'contenance_cl');

        if (! $contenance) {
            $contenance = $this->attributeRepository->create([
                'code' => 'contenance_cl',
                'admin_name' => 'Contenance (cl)',
                'name' => 'Contenance (cl)',
                'type' => 'price',
                'validation' => 'decimal',
                'is_required' => 0,
                'is_unique' => 0,
                'value_per_channel' => 0,
                'value_per_locale' => 0,
                'is_user_defined' => 1,
                'is_filterable' => 0,
                'is_visible_on_front' => 1,
                'is_comparable' => 1,
            ]);
        }

        $degre = $this->attributeRepository->findOneByField('code', 'degre_alcool');

        if (! $degre) {
            $degre = $this->attributeRepository->create([
                'code' => 'degre_alcool',
                'admin_name' => 'Degre alcool',
                'name' => 'Degre alcool',
                'type' => 'price',
                'validation' => 'decimal',
                'is_required' => 0,
                'is_unique' => 0,
                'value_per_channel' => 0,
                'value_per_locale' => 0,
                'is_user_defined' => 1,
                'is_filterable' => 0,
                'is_visible_on_front' => 1,
                'is_comparable' => 1,
            ]);
        }

        // Keep these attributes explicitly usable by search and filtering logic.
        $contenance->forceFill([
            'is_visible_on_front' => 1,
            'is_comparable' => 1,
            'is_filterable' => 1,
        ])->save();

        $degre->forceFill([
            'is_visible_on_front' => 1,
            'is_comparable' => 1,
            'is_filterable' => 1,
        ])->save();

        return [$contenance->fresh(), $degre->fresh()];
    }

    protected function ensureAttributeFamily(Attribute $contenance, Attribute $degre): AttributeFamily
    {
        $family = AttributeFamily::query()->where('code', 'djouf_boissons_simplifie')->first();

        if (! $family) {
            $family = AttributeFamily::query()->create([
                'code' => 'djouf_boissons_simplifie',
                'name' => 'djouf_boissons_simplifie',
            ]);
        }

        $requiredAttributeCodes = [
            'sku',
            'name',
            'description',
            'price',
            'status',
            'visible_individually',
            'url_key',
            'manage_stock',
            'contenance_cl',
            'degre_alcool',
        ];

        $attributeIds = [];

        foreach ($requiredAttributeCodes as $code) {
            $attribute = $this->attributeRepository->findOneByField('code', $code);

            if (! $attribute) {
                throw new \RuntimeException("Attribut introuvable: {$code}");
            }

            $attributeIds[] = $attribute->id;
        }

        $group = $family->attribute_groups()->orderBy('position')->first();

        if (! $group) {
            $group = $family->attribute_groups()->create([
                'code' => 'general',
                'name' => 'General',
                'position' => 1,
                'column' => 1,
                'is_user_defined' => 1,
            ]);
        }

        $syncPayload = [];

        foreach ($attributeIds as $position => $attributeId) {
            $syncPayload[$attributeId] = ['position' => $position + 1];
        }

        $group->custom_attributes()->sync($syncPayload);

        return $family;
    }

    protected function attachFilterableAttributesToManagedCategories(array $attributeIds, array $categoryIds): void
    {
        foreach ($categoryIds as $categoryId) {
            $category = Category::query()->find($categoryId);

            if (! $category) {
                continue;
            }

            $category->filterableAttributes()->syncWithoutDetaching($attributeIds);
        }
    }

    /**
     * @return array{0: array<int, array<int, mixed>>, 1: array<string, int>}
     */
    protected function readRows(string $filePath): array
    {
        $sheet = IOFactory::load($filePath)->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $headers = [];
        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $headers[$column] = (string) $sheet->getCell([$column, 1])->getFormattedValue();
        }

        $headerMap = [];
        foreach ($headers as $column => $header) {
            $normalized = $this->normalizeHeader($header);
            if ($normalized !== '') {
                $headerMap[$normalized] = $column;
            }
        }

        $rows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $line = [];

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $line[$column] = $sheet->getCell([$column, $row])->getFormattedValue();
            }

            $rows[$row] = $line;
        }

        return [$rows, $headerMap];
    }

    protected function normalizeHeader(string $header): string
    {
        $header = $this->normalizeLabel($header);
        $header = str_replace(['(', ')'], '', $header);

        $map = [
            'reference' => 'reference',
            'designation' => 'designation',
            'nom' => 'nom',
            'contenance cl' => 'contenance_cl',
            'categorie' => 'categorie',
            'degre d alcool' => 'degre_alcool',
            'prix ttc' => 'prix_ttc',
        ];

        return $map[$header] ?? str_replace(' ', '_', $header);
    }

    protected function parseDecimal(mixed $value, bool $stripPercent = false): ?float
    {
        if (is_null($value)) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        if ($stripPercent) {
            $raw = str_replace('%', '', $raw);
        }

        $raw = str_replace([' ', "\xC2\xA0"], '', $raw);

        // Keep only digits, minus, comma and dot.
        $raw = preg_replace('/[^0-9,\.\-]/u', '', $raw) ?: '';

        if ($raw === '') {
            return null;
        }

        $lastComma = strrpos($raw, ',');
        $lastDot = strrpos($raw, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } else {
            $raw = str_replace(',', '.', $raw);
        }

        if (! is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    /**
     * Parse price values while preserving thousand groups.
     *
     * Examples:
     * - 21,000 => 21000
     * - 21.000 => 21000
     * - 21 000 => 21000
     */
    protected function parsePrice(mixed $value): ?float
    {
        if (is_null($value)) {
            return null;
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $raw = str_replace([' ', "\xC2\xA0"], '', $raw);
        $raw = preg_replace('/[^0-9,\.\-]/u', '', $raw) ?: '';

        if ($raw === '') {
            return null;
        }

        if (preg_match('/^-?\d{1,3}([,.]\d{3})+$/', $raw)) {
            $normalized = str_replace([',', '.'], '', $raw);

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        $lastComma = strrpos($raw, ',');
        $lastDot = strrpos($raw, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } else {
            if (preg_match('/^-?\d+[,.]\d{3}$/', $raw)) {
                $raw = str_replace([',', '.'], '', $raw);
            } else {
                $raw = str_replace(',', '.', $raw);
            }
        }

        return is_numeric($raw) ? (float) $raw : null;
    }

    protected function mapExcelPathToBusinessPath(string $excelPath): ?string
    {
        if ($excelPath === '') {
            return null;
        }

        $cleanPath = trim($excelPath);
        $segments = array_values(array_filter(array_map('trim', explode('/', $cleanPath))));

        if (empty($segments)) {
            return null;
        }

        $normalizedSegments = array_map(fn (string $segment) => $this->normalizeLabel($segment), $segments);

        $canonicalMap = [
            'vins' => 'Vins',
            'vin rouge' => 'Vin rouge',
            'vin blanc' => 'Vin blanc',
            'vin rose' => 'Vin rose',
            'vin mousseux' => 'Vin mousseux',
            'spiritueux' => 'Spiritueux',
            'whisky' => 'Whisky',
            'rhum' => 'Rhum',
            'vodka' => 'Vodka',
            'gin' => 'Gin',
            'tequila' => 'Tequila',
            'brandy' => 'Brandy',
            'liqueurs' => 'Liqueurs',
            'bieres' => 'Bieres',
            'cidres' => 'Cidres',
        ];

        $businessSegments = ['Boissons alcoolisees'];

        foreach ($normalizedSegments as $segment) {
            if (! isset($canonicalMap[$segment])) {
                return null;
            }

            $businessSegments[] = $canonicalMap[$segment];
        }

        $normalizedBusinessSegments = array_map(fn (string $segment) => $this->normalizeLabel($segment), $businessSegments);

        return implode(' > ', $normalizedBusinessSegments);
    }

    protected function ensureBusinessPathCategory(string $businessPath): int
    {
        $rootCategoryId = core()->getDefaultChannel()->root_category_id;
        $segments = array_map('trim', explode('>', $businessPath));

        $parentId = $rootCategoryId;

        foreach ($segments as $segment) {
            $name = $this->displayCategoryName($segment);
            $category = $this->findCategoryByParentAndName($parentId, $name);

            if (! $category) {
                $category = $this->categoryRepository->create([
                    'locale' => 'all',
                    'name' => $name,
                    'slug' => $this->makeUniqueSlug($name),
                    'description' => $name,
                    'status' => 1,
                    'display_mode' => 'products_only',
                    'parent_id' => $parentId,
                ]);
            }

            $parentId = $category->id;
        }

        return $parentId;
    }

    protected function findCategoryByParentAndName(int $parentId, string $name): ?Category
    {
        return Category::query()
            ->where('parent_id', $parentId)
            ->whereHas('translations', function ($query) use ($name) {
                $query->where('name', $name);
            })
            ->first();
    }

    protected function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (CategoryTranslation::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function normalizeLabel(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = str_replace(['’', "'"], ' ', $value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?: $value;

        return trim($value);
    }

    protected function displayCategoryName(string $normalizedLabel): string
    {
        $label = $this->normalizeLabel($normalizedLabel);

        return match ($label) {
            'boissons alcoolisees' => 'Boissons alcoolisees',
            'vins' => 'Vins',
            'vin rouge' => 'Vin rouge',
            'vin blanc' => 'Vin blanc',
            'vin rose' => 'Vin rose',
            'vin mousseux' => 'Vin mousseux',
            'spiritueux' => 'Spiritueux',
            'whisky' => 'Whisky',
            'rhum' => 'Rhum',
            'vodka' => 'Vodka',
            'gin' => 'Gin',
            'tequila' => 'Tequila',
            'brandy' => 'Brandy',
            'liqueurs' => 'Liqueurs',
            'bieres' => 'Bieres',
            'cidres' => 'Cidres',
            default => Str::title($label),
        };
    }
}

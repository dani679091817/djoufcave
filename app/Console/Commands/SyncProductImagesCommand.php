<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductImageRepository;

class SyncProductImagesCommand extends Command
{
    protected $signature = 'catalog:sync-product-images
                            {--file=Articles_VIN_WHI_enriched.xlsx : Excel file path relative to project root}
                            {--images-dir=images_hd : Image folders directory path relative to project root}
                            {--dry-run : Validate and preview import without writing images}
                            {--skip-existing : Skip products that already have at least one image}';

    protected $description = 'Import product images from images_hd folders by matching Excel designation to folder names and Excel reference to SKU.';

    public function __construct(protected ProductImageRepository $productImageRepository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $excelPath = base_path((string) $this->option('file'));
        $imagesDir = base_path((string) $this->option('images-dir'));
        $dryRun = (bool) $this->option('dry-run');
        $skipExisting = (bool) $this->option('skip-existing');

        if (! file_exists($excelPath)) {
            $this->error("Fichier Excel introuvable: {$excelPath}");

            return self::FAILURE;
        }

        if (! is_dir($imagesDir)) {
            $this->error("Dossier images introuvable: {$imagesDir}");

            return self::FAILURE;
        }

        $rows = $this->readExcelRows($excelPath);

        if (empty($rows)) {
            $this->error('Aucune ligne exploitable trouvee dans le fichier Excel.');

            return self::FAILURE;
        }

        $folderBuckets = $this->buildFolderBuckets($imagesDir);
        $assignments = $this->buildAssignments($rows, $folderBuckets);

        $stats = [
            'rows' => count($rows),
            'mapped' => count($assignments['mapped']),
            'unmapped' => count($assignments['unmapped']),
            'imported' => 0,
            'skipped_existing' => 0,
            'missing_product' => 0,
            'missing_files' => 0,
            'errors' => 0,
        ];

        if (! empty($assignments['unmapped'])) {
            $this->warn('Certains produits n\'ont pas de dossier image associe. Exemples:');

            foreach (array_slice($assignments['unmapped'], 0, 12) as $unmapped) {
                $this->line("- {$unmapped['sku']} | {$unmapped['designation']}");
            }
        }

        foreach ($assignments['mapped'] as $item) {
            $sku = $item['sku'];
            $designation = $item['designation'];
            $folderPath = $item['folder_path'];

            $product = Product::query()->where('sku', $sku)->first();

            if (! $product) {
                $stats['missing_product']++;
                $this->warn("Produit introuvable pour SKU {$sku}.");

                continue;
            }

            if ($skipExisting && $product->images()->exists()) {
                $stats['skipped_existing']++;

                continue;
            }

            $files = $this->collectImageFiles($folderPath);

            if (empty($files)) {
                $stats['missing_files']++;
                $this->warn("Aucune image exploitable pour {$sku} dans {$folderPath}.");

                continue;
            }

            if ($dryRun) {
                $stats['imported']++;

                continue;
            }

            try {
                $uploadedFiles = [];

                foreach ($files as $filePath) {
                    $mimeType = @mime_content_type($filePath) ?: 'image/jpeg';

                    $uploadedFiles[] = new UploadedFile(
                        $filePath,
                        basename($filePath),
                        $mimeType,
                        null,
                        true
                    );
                }

                $this->productImageRepository->upload([
                    'images' => [
                        'files' => $uploadedFiles,
                    ],
                ], $product, 'images');

                $stats['imported']++;
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->warn("Erreur import SKU {$sku} ({$designation}): {$e->getMessage()}");
            }
        }

        $this->table(
            ['rows', 'mapped', 'unmapped', 'imported', 'skipped_existing', 'missing_product', 'missing_files', 'errors'],
            [[
                $stats['rows'],
                $stats['mapped'],
                $stats['unmapped'],
                $stats['imported'],
                $stats['skipped_existing'],
                $stats['missing_product'],
                $stats['missing_files'],
                $stats['errors'],
            ]]
        );

        if ($dryRun) {
            $this->info('Dry-run termine: aucune image n\'a ete ecrite.');
        } else {
            $this->info('Import des images termine.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{sku: string, designation: string, canon: string, strict: string}>
     */
    protected function readExcelRows(string $excelPath): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);

        $sheet = $reader->load($excelPath)->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $rows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $sku = trim((string) $sheet->getCell("A{$row}")->getValue());
            $designation = trim((string) $sheet->getCell("B{$row}")->getValue());

            if ($sku === '' || $designation === '') {
                continue;
            }

            $rows[] = [
                'sku' => $sku,
                'designation' => $designation,
                'canon' => $this->canonicalize($designation),
                'strict' => $this->strictNormalize($designation),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, array<int, array{name: string, path: string, canon: string, strict: string}>>
     */
    protected function buildFolderBuckets(string $imagesDir): array
    {
        $buckets = [];

        foreach (glob($imagesDir.'/*', GLOB_ONLYDIR) as $folderPath) {
            $name = basename($folderPath);

            if ($name === '_rejected') {
                continue;
            }

            $canon = $this->canonicalize($name);

            $buckets[$canon][] = [
                'name' => $name,
                'path' => $folderPath,
                'canon' => $canon,
                'strict' => $this->strictNormalize($name),
            ];
        }

        return $buckets;
    }

    /**
     * @param  array<int, array{sku: string, designation: string, canon: string, strict: string}>  $rows
     * @param  array<string, array<int, array{name: string, path: string, canon: string, strict: string}>>  $folderBuckets
     * @return array{mapped: array<int, array{sku: string, designation: string, folder_name: string, folder_path: string}>, unmapped: array<int, array{sku: string, designation: string}>}
     */
    protected function buildAssignments(array $rows, array $folderBuckets): array
    {
        $rowsByCanon = [];

        foreach ($rows as $row) {
            $rowsByCanon[$row['canon']][] = $row;
        }

        $mapped = [];
        $unmapped = [];

        foreach ($rowsByCanon as $canon => $rowsGroup) {
            $foldersGroup = $folderBuckets[$canon] ?? [];

            if (empty($foldersGroup)) {
                foreach ($rowsGroup as $row) {
                    $unmapped[] = [
                        'sku' => $row['sku'],
                        'designation' => $row['designation'],
                    ];
                }

                continue;
            }

            if (count($rowsGroup) !== count($foldersGroup)) {
                foreach ($rowsGroup as $row) {
                    $unmapped[] = [
                        'sku' => $row['sku'],
                        'designation' => $row['designation'],
                    ];
                }

                continue;
            }

            if (count($rowsGroup) === 1) {
                $mapped[] = [
                    'sku' => $rowsGroup[0]['sku'],
                    'designation' => $rowsGroup[0]['designation'],
                    'folder_name' => $foldersGroup[0]['name'],
                    'folder_path' => $foldersGroup[0]['path'],
                ];

                continue;
            }

            $rowToFolderIndex = $this->solveMinimalDistanceAssignment($rowsGroup, $foldersGroup);

            if ($rowToFolderIndex === null) {
                foreach ($rowsGroup as $row) {
                    $unmapped[] = [
                        'sku' => $row['sku'],
                        'designation' => $row['designation'],
                    ];
                }

                continue;
            }

            foreach ($rowToFolderIndex as $rowIndex => $folderIndex) {
                $mapped[] = [
                    'sku' => $rowsGroup[$rowIndex]['sku'],
                    'designation' => $rowsGroup[$rowIndex]['designation'],
                    'folder_name' => $foldersGroup[$folderIndex]['name'],
                    'folder_path' => $foldersGroup[$folderIndex]['path'],
                ];
            }
        }

        return [
            'mapped' => $mapped,
            'unmapped' => $unmapped,
        ];
    }

    /**
     * @param  array<int, array{sku: string, designation: string, canon: string, strict: string}>  $rows
     * @param  array<int, array{name: string, path: string, canon: string, strict: string}>  $folders
     * @return array<int, int>|null
     */
    protected function solveMinimalDistanceAssignment(array $rows, array $folders): ?array
    {
        $n = count($rows);

        if ($n !== count($folders) || $n === 0 || $n > 7) {
            return null;
        }

        $indices = range(0, $n - 1);
        $bestCost = PHP_INT_MAX;
        $bestPermutation = null;

        $permute = function (array $items, int $l) use (&$permute, $n, &$bestCost, &$bestPermutation, $rows, $folders) {
            if ($l === $n) {
                $cost = 0;

                for ($i = 0; $i < $n; $i++) {
                    $cost += levenshtein($rows[$i]['strict'], $folders[$items[$i]]['strict']);
                }

                if ($cost < $bestCost) {
                    $bestCost = $cost;
                    $bestPermutation = $items;
                }

                return;
            }

            for ($i = $l; $i < $n; $i++) {
                $swapped = $items;
                [$swapped[$l], $swapped[$i]] = [$swapped[$i], $swapped[$l]];
                $permute($swapped, $l + 1);
            }
        };

        $permute($indices, 0);

        if ($bestPermutation === null) {
            return null;
        }

        $assignment = [];

        foreach ($bestPermutation as $rowIndex => $folderIndex) {
            $assignment[$rowIndex] = $folderIndex;
        }

        return $assignment;
    }

    /**
     * @return array<int, string>
     */
    protected function collectImageFiles(string $folderPath): array
    {
        $patterns = [
            $folderPath.'/*.jpg',
            $folderPath.'/*.jpeg',
            $folderPath.'/*.png',
            $folderPath.'/*.webp',
            $folderPath.'/*.JPG',
            $folderPath.'/*.JPEG',
            $folderPath.'/*.PNG',
            $folderPath.'/*.WEBP',
        ];

        $files = [];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $filePath) {
                if (is_file($filePath)) {
                    $files[] = $filePath;
                }
            }
        }

        $files = array_values(array_unique($files));
        natsort($files);

        return array_values($files);
    }

    protected function strictNormalize(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9]+/', '_', $value) ?? '';
        $value = trim($value, '_');

        return preg_replace('/_+/', '_', $value) ?? '';
    }

    protected function canonicalize(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtoupper($value);

        return preg_replace('/[^A-Z0-9]+/', '', $value) ?? '';
    }
}

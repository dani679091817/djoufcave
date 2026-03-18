<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Webkul\Product\Repositories\ProductImageRepository;

class FixProductImages extends Command
{
    protected $signature = 'catalog:fix-product-images {product_id}';
    protected $description = 'Fix missing images for a specific product';

    public function __construct(private ProductImageRepository $productImageRepository)
    {
        parent::__construct();
    }

    public function handle()
    {
        $productId = $this->argument('product_id');
        $product = \Webkul\Product\Models\Product::find($productId);

        if (!$product) {
            $this->error("Produit $productId non trouvé");
            return 1;
        }

        $this->info("Produit trouvé: {$product->sku} - {$product->name}");

        // Déterminer le dossier source basé sur le SKU et le nom
        $sourceDir = $this->findSourceDir($product);

        if (!$sourceDir || !is_dir($sourceDir)) {
            $this->error("Dossier source non trouvé pour le produit");
            return 1;
        }

        $this->info("Dossier source: $sourceDir");

        // Récupérer les fichiers images
        $files = $this->getImageFiles($sourceDir);
        $this->info("Images trouvées: " . count($files));

        if (empty($files)) {
            $this->warn("Aucune image trouvée dans le dossier source");
            return 0;
        }

        $uploadedFiles = [];

        foreach ($files as $file) {
            $sourcePath = "$sourceDir/$file";
            if (!file_exists($sourcePath)) {
                continue;
            }

            $mimeType = @mime_content_type($sourcePath) ?: 'image/jpeg';

            $uploadedFiles[] = new UploadedFile(
                $sourcePath,
                basename($sourcePath),
                $mimeType,
                null,
                true
            );

            $this->line("<info>✓</info> Fichier source: $file");
        }

        if (!empty($uploadedFiles)) {
            try {
                $this->productImageRepository->upload([
                    'images' => [
                        'files' => $uploadedFiles,
                    ],
                ], $product, 'images');

                $this->info("\n<info>✓ Total importé: " . count($uploadedFiles) . " images</info>");
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'upload: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->warn("Aucun fichier à importer");
        }

        return 0;
    }

    private function findSourceDir($product)
    {
        $baseDir = base_path('images_hd');
        
        // Essayer de trouver le dossier basé sur le nom du produit
        $patterns = [
            strtoupper($product->name),
            strtoupper(str_replace(' ', '_', $product->name)),
            strtoupper(str_replace([' ', '-'], '_', $product->name)),
        ];

        foreach ($patterns as $pattern) {
            $path = "$baseDir/$pattern";
            if (is_dir($path)) {
                return $path;
            }
        }

        // Chercher dans les répertoires du dossier images_hd
        if ($handle = opendir($baseDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                // Vérifier si le nom du répertoire correspond partiellement
                if (stripos($entry, 'SING_BARREL') !== false) {
                    return "$baseDir/$entry";
                }
            }
            closedir($handle);
        }

        return null;
    }

    private function getImageFiles($dir)
    {
        $files = array_filter(scandir($dir), function ($f) {
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });

        sort($files);
        return $files;
    }
}

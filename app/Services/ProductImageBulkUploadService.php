<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use Exception;

class ProductImageBulkUploadService
{
    private array $supportedFormats = ['jpg', 'jpeg', 'png', 'webp'];
    private int $maxFileSize = 2048; // 2MB in KB
    private array $results = [];

    public function processZipFile(UploadedFile $zipFile): array
    {
        $this->results = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'details' => []
        ];

        // Validar que sea un archivo ZIP
        if ($zipFile->getClientOriginalExtension() !== 'zip') {
            throw new Exception('El archivo debe ser un ZIP válido.');
        }

        $tempPath = $zipFile->store('temp');
        $fullPath = Storage::path($tempPath);

        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== TRUE) {
            Storage::delete($tempPath);
            throw new Exception('No se pudo abrir el archivo ZIP.');
        }

        try {
            $this->processZipContents($zip);
        } finally {
            $zip->close();
            Storage::delete($tempPath);
        }

        return $this->results;
    }

    private function processZipContents(ZipArchive $zip): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Saltar directorios y archivos ocultos
            if (str_ends_with($filename, '/') || str_starts_with(basename($filename), '.')) {
                continue;
            }

            $this->processImageFile($zip, $filename, $i);
        }
    }

    private function processImageFile(ZipArchive $zip, string $filename, int $index): void
    {
        $pathInfo = pathinfo($filename);
        $extension = strtolower($pathInfo['extension'] ?? '');
        $basename = $pathInfo['filename'];

        // Validar formato de imagen
        if (!in_array($extension, $this->supportedFormats)) {
            $this->addResult('skipped', $filename, "Formato no soportado: {$extension}");
            return;
        }

        // Buscar producto por código
        $product = Product::where('code', $basename)->first();
        if (!$product) {
            $this->addResult('skipped', $filename, "No se encontró producto con código: {$basename}");
            return;
        }

        try {
            // Extraer contenido del archivo
            $imageContent = $zip->getFromIndex($index);
            if ($imageContent === false) {
                $this->addResult('errors', $filename, 'No se pudo extraer el archivo del ZIP');
                return;
            }

            // Validar tamaño
            $sizeInKB = strlen($imageContent) / 1024;
            if ($sizeInKB > $this->maxFileSize) {
                $this->addResult('errors', $filename, "Archivo muy grande: {$sizeInKB}KB (máximo: {$this->maxFileSize}KB)");
                return;
            }

            // Eliminar imagen anterior si existe
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Guardar nueva imagen
            $newFilename = 'products/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($newFilename, $imageContent);

            // Actualizar producto
            $product->update(['image_path' => $newFilename]);

            $this->addResult('success', $filename, "Imagen asignada al producto: {$product->name}");

        } catch (Exception $e) {
            $this->addResult('errors', $filename, 'Error al procesar: ' . $e->getMessage());
        }
    }

    private function addResult(string $type, string $filename, string $message): void
    {
        $this->results[$type]++;
        $this->results['details'][] = [
            'type' => $type,
            'filename' => $filename,
            'message' => $message
        ];
    }

    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }
}
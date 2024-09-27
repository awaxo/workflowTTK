<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;

class ImportManager
{
    protected $importers = [];

    public function registerImporter(string $key, BaseImporter $importer)
    {
        $this->importers[$key] = $importer;
    }

    public function import(string $key, UploadedFile $file): array
    {
        if (!isset($this->importers[$key])) {
            throw new \Exception("Importer for key {$key} not found.");
        }

        return $this->importers[$key]->import($file);
    }
}

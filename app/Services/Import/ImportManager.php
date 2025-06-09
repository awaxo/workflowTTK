<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;

/*
 * ImportManager is responsible for managing different importers.
 * It allows registering importers and importing files using the registered importers.
 */
class ImportManager
{
    protected $importers = [];

    /**
     * Register an importer with a specific key.
     *
     * @param string $key
     * @param BaseImporter $importer
     */
    public function registerImporter(string $key, BaseImporter $importer)
    {
        $this->importers[$key] = $importer;
    }

    /*
     * Import a file using the specified importer key.
     *
     * @param string $key
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    public function import(string $key, UploadedFile $file): array
    {
        if (!isset($this->importers[$key])) {
            throw new \Exception("Importer for key {$key} not found.");
        }

        return $this->importers[$key]->import($file);
    }
}

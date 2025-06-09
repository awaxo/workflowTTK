<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

/*
 * BaseImporter is an abstract class that provides a structure for importing CSV files.
 * It includes methods for validating the file, parsing the CSV, validating each row,
 * and saving the data. Subclasses must implement the rules and saveRow methods.
 */
abstract class BaseImporter
{
    protected $errors = [];

    /**
     * Import a CSV file and return an array of errors if any.
     *
     * @param UploadedFile $file
     * @return array
     */
    public function import(UploadedFile $file): array
    {
        if (!$this->isValidCSV($file)) {
            $this->errors[] = 'The file is not a valid CSV.';
            return $this->errors;
        }

        $data = $this->parseCSV($file);

        foreach ($data as $index => $row) {
            if (!$this->validateRow($row, $index)) {
                continue;
            }
        }

        if (!empty($this->errors)) {
            return $this->errors;
        }

        foreach ($data as $index => $row) {
            $this->saveRow($row, $index);
        }

        return $this->errors;
    }

    /**
     * Check if the uploaded file is a valid CSV.
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function isValidCSV(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        return $mimeType === 'text/plain' && str_contains($content, ';');
    }

    /**
     * Parse the CSV file and return an array of rows.
     *
     * @param UploadedFile $file
     * @return array
     */
    protected function parseCSV(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $data[] = $row;
        }

        fclose($handle);

        return $data;
    }

    /**
     * Validate a single row of data.
     *
     * @param array $row
     * @param int $index
     * @return bool
     */
    protected function validateRow(array $row, int $index): bool
    {
        $validator = Validator::make($row, $this->rules());

        if ($validator->fails()) {
            $this->errors[$index] = $validator->errors()->all();
            return false;
        }

        return true;
    }

    /**
     * Define the validation rules for each row.
     *
     * @return array
     */
    abstract protected function rules(): array;

    /**
     * Save a single row of data.
     *
     * @param array $row
     * @param int $index
     * @return void
     */
    abstract protected function saveRow(array $row, int $index): void;
}

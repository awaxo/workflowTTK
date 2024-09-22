<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

abstract class BaseImporter
{
    protected $errors = [];

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

    protected function isValidCSV(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        return $mimeType === 'text/plain' && str_contains($content, ';');
    }

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

    protected function validateRow(array $row, int $index): bool
    {
        $validator = Validator::make($row, $this->rules());

        if ($validator->fails()) {
            $this->errors[$index] = $validator->errors()->all();
            return false;
        }

        return true;
    }

    abstract protected function rules(): array;

    abstract protected function saveRow(array $row, int $index): void;
}

<?php

namespace App\Services\ImportExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class ImportExportService
{
    /**
     * Parse CSV into rows keyed by mapped columns.
     */
    public function parse(string $filePath, array $columnMap): array
    {
        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setHeaderOffset(0);
        $rows = [];

        foreach ($reader->getRecords() as $record) {
            $mapped = [];
            foreach ($columnMap as $field => $header) {
                $mapped[$field] = $record[$header] ?? null;
            }
            $rows[] = $mapped;
        }

        return $rows;
    }

    /**
     * Validate imported rows against rules; returns [valid, errors].
     */
    public function validateRows(Collection $rows, array $rules): array
    {
        $valid = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, $rules);
            if ($validator->fails()) {
                $errors[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->toArray(),
                ];
            } else {
                $valid[] = $row;
            }
        }

        return [$valid, $errors];
    }

    /**
     * Build a CSV writer with a header row.
     */
    public function writer(array $headers, Collection $rows): string
    {
        $writer = Writer::createFromString('');
        $writer->insertOne($headers);

        foreach ($rows as $row) {
            $writer->insertOne(array_map(
                fn ($h) => $row[$h] ?? null,
                $headers
            ));
        }

        return $writer->toString();
    }
}

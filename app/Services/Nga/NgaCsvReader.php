<?php

namespace App\Services\Nga;

use Generator;
use SplFileObject;
use RuntimeException;

/**
 * Streaming reader for NGA Open Data CSV files. Yields header-mapped rows
 * one at a time so full files never load into memory. Handles quoted
 * multi-line fields (SplFileObject CSV mode) and a UTF-8 BOM on the first
 * header.
 */
class NgaCsvReader
{
    /**
     * @return Generator<int, array<string, string>>
     */
    public function rows(string $filePath): Generator
    {
        if (!is_readable($filePath)) {
            throw new RuntimeException("NGA CSV file is missing or unreadable: {$filePath}");
        }

        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);

        $headers = null;

        foreach ($file as $row) {
            if (!is_array($row) || $row === [null]) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($row);

                continue;
            }

            // Tolerate ragged rows rather than dying mid-file.
            $row = array_slice(array_pad($row, count($headers), null), 0, count($headers));

            yield array_combine($headers, array_map(fn($value) => $value === null ? '' : (string) $value, $row));
        }
    }

    /**
     * @param  array<int, string|null>  $headerRow
     * @return array<int, string>
     */
    protected function normalizeHeaders(array $headerRow): array
    {
        $headers = array_map(fn($header) => trim((string) $header), $headerRow);

        // Strip a UTF-8 BOM from the first header if present.
        if (isset($headers[0])) {
            $headers[0] = ltrim($headers[0], "\u{FEFF}");
        }

        return array_map('strtolower', $headers);
    }
}

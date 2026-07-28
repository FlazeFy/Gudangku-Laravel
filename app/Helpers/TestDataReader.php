<?php

namespace App\Helpers;

class TestDataReader
{
    private static function getPath(): string
    {
        return dirname(__DIR__, 2) . '/tests/TestData/data.csv';
    }

    public static function getValue(string $key): ?string
    {
        self::ensureFileExists();

        $handle = fopen(self::getPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('Failed to read test data');
        }

        // Skip header
        fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2 && $row[0] === $key) {
                fclose($handle);
                return $row[1];
            }
        }

        fclose($handle);

        return null;
    }

    public static function setValue(string $key, string $value): void
    {
        self::ensureFileExists();

        $rows = array_map('str_getcsv', file(self::getPath()));
        $found = false;
        for ($i = 1; $i < count($rows); $i++) {
            if (isset($rows[$i][0]) && $rows[$i][0] === $key) {
                $rows[$i] = [
                    $key,
                    $value,
                    now()->format('Y-m-d H:i:s'),
                ];

                $found = true;
                break;
            }
        }

        if (!$found) {
            $rows[] = [
                $key,
                $value,
                now()->format('Y-m-d H:i:s'),
            ];
        }

        $handle = fopen(self::getPath(), 'w');
        if ($handle === false) throw new \RuntimeException('Failed to save test data');

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    private static function ensureFileExists(): void
    {
        $path = self::getPath();
        if (file_exists($path)) return;

        $directory = dirname($path);
        if (!is_dir($directory)) mkdir($directory, 0777, true);

        $handle = fopen($path, 'w');
        if ($handle === false) throw new \RuntimeException('Failed to create test data file');

        fputcsv($handle, ['key', 'value', 'created_at',]);

        fclose($handle);
    }

    public static function clear(): void
    {
        $handle = fopen(self::getPath(), 'w');
        if ($handle === false) {
            throw new \RuntimeException('Failed to clear test data');
        }

        fputcsv($handle, ['key', 'value', 'created_at']);
        fclose($handle);
    }
}
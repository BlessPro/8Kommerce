<?php

namespace App\Support;

class Csv
{
    public static function row(array $values): array
    {
        return array_map([self::class, 'cell'], $values);
    }

    public static function cell(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}

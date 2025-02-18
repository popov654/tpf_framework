<?php

namespace Tpf\Validator;

class StringFormat
{
    public static function notEmpty(string $value): bool
    {
        return !preg_match("/^\\s*$/", $value);
    }

    public static function isLatinAlphaNumeric(string $value): bool
    {
        return preg_match("/^[a-z0-9_-]+$/i", $value);
    }

    public static function isEmail(string $value): bool
    {
        return preg_match("/^[a-z][a-z\d~._-]*[a-z\d]@[a-z][a-z\d~._-]*[a-z\d]$/", $value);
    }

    public static function notShorterThan(string $value, int $size): bool
    {
        return strlen($value) >= $size;
    }

    public static function longerThan(string $value, int $size): bool
    {
        return strlen($value) > $size;
    }

    public static function notLongerThan(string $value, int $size): bool
    {
        return strlen($value) <= $size;
    }

    public static function shorterThan(string $value, int $size): bool
    {
        return strlen($value) < $size;
    }
}
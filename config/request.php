<?php
declare(strict_types=1);

function requestScalar(array $source, string $key): string
{
    $value = $source[$key] ?? '';

    return is_scalar($value) ? trim((string) $value) : '';
}

function requestText(array $source, string $key, int $maxLength): string
{
    $value = requestScalar($source, $key);

    return strlen($value) <= $maxLength ? $value : '';
}

function requestEnum(array $source, string $key, array $allowed): string
{
    $value = requestScalar($source, $key);

    return in_array($value, $allowed, true) ? $value : '';
}

function requestDate(array $source, string $key): ?DateTimeImmutable
{
    $value = requestScalar($source, $key);
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();

    return $date
        && ($errors === false || (($errors['warning_count'] ?? 1) === 0 && ($errors['error_count'] ?? 1) === 0))
        && $date->format('Y-m-d') === $value
        ? $date
        : null;
}

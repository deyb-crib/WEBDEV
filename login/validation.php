<?php
require_once __DIR__ . '/../config/request.php';

function validateRequired(string $value, string $fieldName): ?string
{
    return trim($value) === '' ? "{$fieldName} is required." : null;
}

function validateEmail(string $value): ?string
{
    return filter_var($value, FILTER_VALIDATE_EMAIL)
        ? null
        : 'Enter a valid email address.';
}

function validatePasswordStrength(string $value): ?string
{
    if (strlen($value) < 8) {
        return 'Password must be at least 8 characters.';
    }

    return null;
}

function validateLoginInput(array $post): array
{
    $email = strtolower(requestText($post, 'email', 254));
    $password = requestScalar($post, 'password');

    $errors = array_filter([
        validateRequired($email, 'Email address'),
        validateEmail($email),
        validateRequired($password, 'Password'),
        validatePasswordStrength($password),
    ]);

    return [
        'errors' => array_values($errors),
        'data' => [
            'email' => $email,
            'password' => $password,
        ],
    ];
}

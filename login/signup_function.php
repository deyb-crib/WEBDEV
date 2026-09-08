<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/request.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = requestText($_POST, 'name', 100);
    $email = strtolower(requestText($_POST, 'email', 254));
    $password = requestScalar($_POST, 'password');
    $confirmPassword = requestScalar($_POST, 'confirm_password');

    if (strlen($name) < 2 || !preg_match('/^[\p{L}\p{M} .\'\-]+$/u', $name)) {
        $errors[] = 'Enter your full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    if (!$errors) {
        try {
            $connection = getDatabaseConnection();
            $statement = $connection->prepare(
                'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
            );
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $connection->lastInsertId(),
                'name' => $name,
                'email' => $email,
            ];
            header('Location: ../dashboard/dashboard.php');
            exit;
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000'
                ? 'An account with this email already exists.'
                : 'Unable to save your account. Please try again later.';
        }
    }
}

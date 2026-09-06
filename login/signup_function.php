<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (mb_strlen($name) < 2) {
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

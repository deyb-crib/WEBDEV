<?php
session_start();
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/../config/database.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validation = validateLoginInput($_POST);
    $errors = $validation['errors'];
    $email = $validation['data']['email'];

    if (!$errors) {
        try {
            $statement = getDatabaseConnection()->prepare(
                'SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute(['email' => $email]);
            $user = $statement->fetch();

            if (!$user || !password_verify($validation['data']['password'], $user['password_hash'])) {
                $errors[] = 'Invalid email or password.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ];
                header('Location: ../dashboard/dashboard.php');
                exit;
            }
        } catch (PDOException $exception) {
            $errors[] = 'Unable to connect to the user database. Please try again later.';
        }
    }
}

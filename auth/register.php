<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (!auth_valid_csrf()) {
    auth_flash('error', 'up', 'Your registration session expired. Please try again.');
    header('Location: ../index.php?auth=register');
    exit;
}

$nameInput = $_POST['name'] ?? '';
$businessInput = $_POST['business'] ?? '';
$emailInput = $_POST['email'] ?? '';
$name = is_string($nameInput) ? trim($nameInput) : '';
$business = is_string($businessInput) ? trim($businessInput) : '';
$email = is_string($emailInput)
    ? filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL)
    : false;
$password = $_POST['password'] ?? '';
$acceptedTerms = isset($_POST['terms']);

if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    auth_flash('error', 'up', 'Enter your name using 2 to 120 characters.');
    header('Location: ../index.php?auth=register');
    exit;
}
if ($business !== '' && mb_strlen($business) > 160) {
    auth_flash('error', 'up', 'Business name must be 160 characters or fewer.');
    header('Location: ../index.php?auth=register');
    exit;
}
if ($email === false) {
    auth_flash('error', 'up', 'Enter a valid email address.');
    header('Location: ../index.php?auth=register');
    exit;
}
if (
    !is_string($password)
    || strlen($password) < 8
    || !preg_match('/[A-Za-z]/', $password)
    || !preg_match('/\d/', $password)
) {
    auth_flash('error', 'up', 'Use at least 8 characters with a letter and a number.');
    header('Location: ../index.php?auth=register');
    exit;
}
if (!$acceptedTerms) {
    auth_flash('error', 'up', 'Please accept the terms to create your account.');
    header('Location: ../index.php?auth=register');
    exit;
}

try {
    $pdo = auth_db();
    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);
    if ($check->fetch()) {
        auth_flash('error', 'up', 'An account with that email already exists. Sign in instead.');
        header('Location: ../index.php?auth=register');
        exit;
    }

    $insert = $pdo->prepare(
        'INSERT INTO users (full_name, business_name, email, password_hash)
         VALUES (:full_name, :business_name, :email, :password_hash)'
    );
    $insert->execute([
        'full_name' => $name,
        'business_name' => $business !== '' ? $business : null,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $exception) {
    error_log('Registration database error: ' . $exception->getMessage());
    $message = $exception->getCode() === '23000'
        ? 'An account with that email already exists. Sign in instead.'
        : 'Unable to create your account. Check that MySQL is running and mari_flow_db exists.';
    auth_flash('error', 'up', $message);
    header('Location: ../index.php?auth=register');
    exit;
}

auth_flash('success', 'up', 'Your account was created successfully. You can now sign in.');
header('Location: ../index.php?auth=register');
exit;

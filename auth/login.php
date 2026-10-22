<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

if (!auth_valid_csrf()) {
    auth_flash('error', 'in', 'Your sign-in session expired. Please try again.');
    header('Location: ../index.php?auth=login');
    exit;
}

$emailInput = $_POST['email'] ?? '';
$email = is_string($emailInput)
    ? filter_var(trim($emailInput), FILTER_VALIDATE_EMAIL)
    : false;
$password = $_POST['password'] ?? '';
if ($email === false || !is_string($password) || $password === '') {
    auth_flash('error', 'in', 'Enter a valid email address and password.');
    header('Location: ../index.php?auth=login');
    exit;
}

try {
    $statement = auth_db()->prepare(
        'SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
} catch (PDOException $exception) {
    error_log('Login database error: ' . $exception->getMessage());
    auth_flash('error', 'in', 'Unable to reach the account database. Check that MySQL is running and mari_flow_db exists.');
    header('Location: ../index.php?auth=login');
    exit;
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    auth_flash('error', 'in', 'The email or password is incorrect.');
    header('Location: ../index.php?auth=login');
    exit;
}

session_regenerate_id(true);
$_SESSION['auth_user'] = [
    'id' => (int) $user['id'],
    'name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role'],
];
if ($user['role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}
$_SESSION['dashboard_notice'] = 'Signed in successfully. Welcome back, ' . $user['full_name'] . '.';
header('Location: ../user/overview.php');
exit;

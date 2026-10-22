<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/_bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function role_response(int $status, array $response): never
{
    http_response_code($status);
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    role_response(405, ['success' => false, 'message' => 'Use POST to change an account role.']);
}
if (!isset($_SESSION['auth_user']['id'])) {
    role_response(401, ['success' => false, 'message' => 'Sign in as an administrator to change roles.']);
}
if (!auth_valid_csrf()) {
    role_response(403, ['success' => false, 'message' => 'Your admin session expired. Refresh and try again.']);
}

$userIdInput = $_POST['user_id'] ?? '';
$role = $_POST['role'] ?? '';
$userId = is_string($userIdInput) && ctype_digit($userIdInput) ? (int) $userIdInput : 0;
if ($userId < 1 || !is_string($role) || !in_array($role, ['admin', 'user'], true)) {
    role_response(422, ['success' => false, 'message' => 'Choose a valid account and role.']);
}

try {
    $pdo = auth_db();
    $pdo->beginTransaction();

    $actorQuery = $pdo->prepare('SELECT role FROM users WHERE id = :id FOR UPDATE');
    $actorQuery->execute(['id' => (int) $_SESSION['auth_user']['id']]);
    if ($actorQuery->fetchColumn() !== 'admin') {
        $pdo->rollBack();
        role_response(403, ['success' => false, 'message' => 'Administrator access is required to change roles.']);
    }

    $targetQuery = $pdo->prepare('SELECT id, role FROM users WHERE id = :id FOR UPDATE');
    $targetQuery->execute(['id' => $userId]);
    $target = $targetQuery->fetch();
    if (!$target) {
        $pdo->rollBack();
        role_response(404, ['success' => false, 'message' => 'That account no longer exists.']);
    }

    if ($target['role'] === $role) {
        $pdo->commit();
        role_response(200, ['success' => true, 'role' => $role, 'message' => 'Role is already set to ' . $role . '.']);
    }
    if ($role === 'user' && (int) $target['id'] === (int) $_SESSION['auth_user']['id']) {
        $pdo->rollBack();
        role_response(422, ['success' => false, 'message' => 'You cannot remove your own admin role.']);
    }

    if ($role === 'user' && $target['role'] === 'admin') {
        $adminQuery = $pdo->query("SELECT id FROM users WHERE role = 'admin' FOR UPDATE");
        if (count($adminQuery->fetchAll()) <= 1) {
            $pdo->rollBack();
            role_response(422, ['success' => false, 'message' => 'At least one administrator account must remain.']);
        }
    }

    $update = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
    $update->execute(['role' => $role, 'id' => $userId]);
    $pdo->commit();
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Admin role update database error: ' . $exception->getMessage());
    role_response(500, ['success' => false, 'message' => 'Unable to save the account role.']);
}

role_response(200, [
    'success' => true,
    'role' => $role,
    'message' => 'Account role changed to ' . $role . '.',
]);

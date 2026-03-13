<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

session_start();

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function require_role(string $role): void
{
    require_login();
    if ((current_user()['role_name'] ?? '') !== $role) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Недостаточно прав для доступа.</p>';
        exit;
    }
}

function login_user(string $login, string $password): bool
{
    $stmt = db()->prepare('SELECT u.id, u.login, u.password_hash, r.name AS role_name, u.role_id
                           FROM users u
                           JOIN roles r ON r.id = u.role_id
                           WHERE u.login = :login');
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

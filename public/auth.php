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


function register_user(string $login, string $password): array
{
    $login = trim($login);
    if ($login === '' || mb_strlen($login) < 3) {
        return [false, 'Логин должен содержать минимум 3 символа.'];
    }
    if (strlen($password) < 6) {
        return [false, 'Пароль должен содержать минимум 6 символов.'];
    }

    $pdo = db();
    $check = $pdo->prepare('SELECT id FROM users WHERE login = :login');
    $check->execute(['login' => $login]);
    if ($check->fetchColumn()) {
        return [false, 'Пользователь с таким логином уже существует.'];
    }

    $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
    $roleStmt->execute(['name' => 'user']);
    $roleId = (int) $roleStmt->fetchColumn();

    if ($roleId <= 0) {
        $fallback = $pdo->query("SELECT id FROM roles WHERE name <> 'admin' ORDER BY id ASC LIMIT 1")->fetchColumn();
        $roleId = (int) $fallback;
    }

    if ($roleId <= 0) {
        return [false, 'Не найдена роль для регистрации (создайте роль user в таблице roles).'];
    }

    $stmt = $pdo->prepare('INSERT INTO users (login, password_hash, role_id) VALUES (:login, :pass, :role_id)');
    $stmt->execute([
        'login' => $login,
        'pass' => password_hash($password, PASSWORD_DEFAULT),
        'role_id' => $roleId,
    ]);

    return [true, 'Регистрация успешна. Теперь войдите в систему.'];
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

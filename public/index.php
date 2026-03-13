<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$page = $_GET['page'] ?? (current_user() ? 'dashboard' : 'login');

if ($page === 'login' || $page === 'register') {
    if (current_user()) {
        redirect('index.php');
    }

    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($page === 'login') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            if (login_user($login, $password)) {
                redirect('index.php');
            }
            $error = 'Неверный логин или пароль';
        }

        if ($page === 'register') {
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            if ($password !== $passwordConfirm) {
                $error = 'Пароли не совпадают.';
            } else {
                [$ok, $message] = register_user($login, $password);
                if ($ok) {
                    $success = $message;
                } else {
                    $error = $message;
                }
            }
        }
    }

    render_header($page === 'login' ? 'Вход в систему' : 'Регистрация');
    ?>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-3"><?= $page === 'login' ? 'Авторизация' : 'Регистрация пользователя' ?></h3>
                    <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

                    <?php if ($page === 'login'): ?>
                        <form method="post" class="vstack gap-3">
                            <input class="form-control" name="login" placeholder="Логин" required>
                            <input class="form-control" name="password" type="password" placeholder="Пароль" required>
                            <button class="btn btn-primary">Войти</button>
                        </form>
                        <a class="btn btn-link mt-2 px-0" href="index.php?page=register">Нет аккаунта? Зарегистрироваться</a>
                        <p class="small text-muted mt-3 mb-0">Если пользователей нет, выполните SQL и добавьте администратора вручную.</p>
                    <?php else: ?>
                        <form method="post" class="vstack gap-3">
                            <input class="form-control" name="login" placeholder="Логин" required>
                            <input class="form-control" name="password" type="password" placeholder="Пароль (минимум 6 символов)" required>
                            <input class="form-control" name="password_confirm" type="password" placeholder="Повторите пароль" required>
                            <button class="btn btn-success">Зарегистрироваться</button>
                        </form>
                        <a class="btn btn-link mt-2 px-0" href="index.php?page=login">Уже есть аккаунт? Войти</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    render_footer();
    exit;
}

require_login();

if (is_admin() && $page !== 'users') {
    redirect('index.php?page=users');
}
if (!is_admin() && $page === 'users') {
    http_response_code(403);
    exit('Недостаточно прав.');
}

switch ($page) {
    case 'dashboard':
        $stats = [
            'total_batches' => (int) db()->query('SELECT COUNT(*) FROM batches')->fetchColumn(),
            'total_stock' => (int) db()->query('SELECT COALESCE(SUM(quantity),0) FROM batches')->fetchColumn(),
            'expiring' => (int) db()->query("SELECT COUNT(*) FROM batches WHERE expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn(),
        ];
        $expiringList = db()->query("SELECT b.batch_number, b.expiration_date, b.quantity, m.name
                                     FROM batches b JOIN medicines m ON m.id=b.medicine_id
                                     WHERE b.expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                     ORDER BY b.expiration_date ASC LIMIT 10")->fetchAll();
        render_header('Панель склада');
        ?>
        <h2 class="mb-4">Сводка по складу</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card stat-card"><div class="card-body"><h6>Всего партий</h6><div class="display-6"><?= $stats['total_batches'] ?></div></div></div></div>
            <div class="col-md-4"><div class="card stat-card"><div class="card-body"><h6>Остаток (ед.)</h6><div class="display-6"><?= $stats['total_stock'] ?></div></div></div></div>
            <div class="col-md-4"><div class="card stat-card"><div class="card-body"><h6>Истекают ≤30 дней</h6><div class="display-6 text-danger"><?= $stats['expiring'] ?></div></div></div></div>
        </div>
        <div class="card"><div class="card-body">
            <h5>Ближайшие сроки годности</h5>
            <table class="table table-sm align-middle">
                <thead><tr><th>Препарат</th><th>Партия</th><th>Срок</th><th>Остаток</th></tr></thead>
                <tbody>
                <?php foreach ($expiringList as $row): ?>
                    <tr><td><?= h($row['name']) ?></td><td><?= h($row['batch_number']) ?></td><td><?= h($row['expiration_date']) ?></td><td><?= (int) $row['quantity'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
        <?php
        render_footer();
        break;

    case 'users':
        require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $stmt = db()->prepare('INSERT INTO users (login, password_hash, role_id) VALUES (:login,:pass,:role_id)');
                $stmt->execute([
                    'login' => trim($_POST['login']),
                    'pass' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'role_id' => (int) $_POST['role_id'],
                ]);
                flash('Пользователь создан.');
            } elseif ($action === 'update') {
                $params = ['id' => (int) $_POST['id'], 'login' => trim($_POST['login']), 'role_id' => (int) $_POST['role_id']];
                if (!empty($_POST['password'])) {
                    $params['pass'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    db()->prepare('UPDATE users SET login=:login, role_id=:role_id, password_hash=:pass WHERE id=:id')->execute($params);
                } else {
                    db()->prepare('UPDATE users SET login=:login, role_id=:role_id WHERE id=:id')->execute($params);
                }
                flash('Пользователь обновлён.');
            } elseif ($action === 'delete') {
                db()->prepare('DELETE FROM users WHERE id=:id')->execute(['id' => (int) $_POST['id']]);
                flash('Пользователь удалён.');
            }
            redirect('index.php?page=users');
        }
        $q = trim($_GET['q'] ?? '');
        $roles = db()->query('SELECT id, name FROM roles ORDER BY name')->fetchAll();
        $stmt = db()->prepare('SELECT u.id,u.login,u.created_at,r.name AS role_name,u.role_id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.login LIKE :q ORDER BY u.id DESC');
        $stmt->execute(['q' => '%' . $q . '%']);
        $rows = $stmt->fetchAll();
        render_header('Пользователи');
        include __DIR__ . '/views_users.php';
        render_footer();
        break;

    default:
        if (!is_admin()) {
            require_once __DIR__ . '/modules.php';
            handle_user_module($page);
            break;
        }
        http_response_code(404);
        echo 'Страница не найдена';
}

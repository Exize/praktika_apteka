<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $msg = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $msg;
}

function is_admin(): bool
{
    return (current_user()['role_name'] ?? '') === 'admin';
}

function render_header(string $title): void
{
    $user = current_user();
    ?>
    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="assets/style.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <?php if ($user): ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">Аптека ЛПУ</a>
                <div class="collapse navbar-collapse show">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php if (is_admin()): ?>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=users">Пользователи</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard">Склад</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=medicines">Препараты</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=suppliers">Поставщики</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=departments">Отделения</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=batches">Партии</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=receipts">Поступления</a></li>
                            <li class="nav-item"><a class="nav-link" href="index.php?page=write_offs">Списания</a></li>
                        <?php endif; ?>
                    </ul>
                    <span class="text-white me-3"><?= h($user['login']) ?> (<?= h($user['role_name']) ?>)</span>
                    <a class="btn btn-outline-light btn-sm" href="logout.php">Выйти</a>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    <main class="container py-4">
        <?php if ($msg = flash()): ?>
            <div class="alert alert-info"><?= h($msg) ?></div>
        <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
    </body>
    </html>
    <?php
}

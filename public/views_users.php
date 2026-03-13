<h2 class="mb-3">Управление пользователями</h2>
<div class="card mb-4"><div class="card-body">
    <form class="row g-2" method="get">
        <input type="hidden" name="page" value="users">
        <div class="col-md-4"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Поиск по логину"></div>
        <div class="col-auto"><button class="btn btn-outline-primary">Поиск</button></div>
    </form>
</div></div>

<div class="card mb-4"><div class="card-body">
    <h5>Добавить пользователя</h5>
    <form class="row g-2" method="post">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><input class="form-control" name="login" placeholder="Логин" required></div>
        <div class="col-md-3"><input class="form-control" name="password" placeholder="Пароль" required></div>
        <div class="col-md-3">
            <select class="form-select" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id'] ?>"><?= h($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><button class="btn btn-primary w-100">Создать</button></div>
    </form>
</div></div>

<div class="card"><div class="card-body">
    <table class="table table-hover align-middle">
        <thead><tr><th>ID</th><th>Логин</th><th>Роль</th><th>Создан</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <form method="post">
                    <td><?= (int) $row['id'] ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"></td>
                    <td><input class="form-control" name="login" value="<?= h($row['login']) ?>" required></td>
                    <td>
                        <select class="form-select" name="role_id">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= (int) $role['id'] ?>" <?= $row['role_id'] == $role['id'] ? 'selected' : '' ?>><?= h($role['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><?= h($row['created_at']) ?></td>
                    <td>
                        <input class="form-control mb-1" type="password" name="password" placeholder="Новый пароль (опц.)">
                        <button class="btn btn-sm btn-success" name="action" value="update">Сохранить</button>
                        <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm('Удалить пользователя?')">Удалить</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div></div>

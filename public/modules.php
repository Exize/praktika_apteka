<?php

declare(strict_types=1);

function handle_user_module(string $page): void
{
    switch ($page) {
        case 'medicines':
            crud_simple(
                'medicines',
                ['name' => 'Наименование', 'manufacturer' => 'Производитель', 'form' => 'Форма', 'dosage' => 'Дозировка'],
                'name'
            );
            return;
        case 'suppliers':
            crud_simple(
                'suppliers',
                ['name' => 'Наименование', 'phone' => 'Телефон', 'email' => 'Email'],
                'name'
            );
            return;
        case 'departments':
            crud_simple(
                'departments',
                ['name' => 'Отделение'],
                'name'
            );
            return;
        case 'batches':
            module_batches();
            return;
        case 'receipts':
            module_receipts();
            return;
        case 'write_offs':
            module_writeoffs();
            return;
        default:
            redirect('index.php?page=dashboard');
    }
}

function crud_simple(string $table, array $fields, string $searchField): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $columns = array_keys($fields);
            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', $columns), implode(',', array_map(fn($c) => ':' . $c, $columns)));
            $params = [];
            foreach ($columns as $c) {
                $params[$c] = trim($_POST[$c] ?? '');
            }
            db()->prepare($sql)->execute($params);
            flash('Запись добавлена.');
        } elseif ($action === 'update') {
            $columns = array_keys($fields);
            $set = implode(',', array_map(fn($c) => "$c=:$c", $columns));
            $sql = sprintf('UPDATE %s SET %s WHERE id=:id', $table, $set);
            $params = ['id' => (int) $_POST['id']];
            foreach ($columns as $c) {
                $params[$c] = trim($_POST[$c] ?? '');
            }
            db()->prepare($sql)->execute($params);
            flash('Запись обновлена.');
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM {$table} WHERE id=:id")->execute(['id' => (int) $_POST['id']]);
            flash('Запись удалена.');
        }
        redirect('index.php?page=' . $table);
    }

    $q = trim($_GET['q'] ?? '');
    $stmt = db()->prepare("SELECT * FROM {$table} WHERE {$searchField} LIKE :q ORDER BY id DESC");
    $stmt->execute(['q' => '%' . $q . '%']);
    $rows = $stmt->fetchAll();

    render_header(ucfirst($table));
    ?>
    <h2 class="mb-3"><?= h(ucfirst($table)) ?></h2>
    <div class="card mb-3"><div class="card-body">
        <form class="row g-2" method="get">
            <input type="hidden" name="page" value="<?= h($table) ?>">
            <div class="col-md-5"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Поиск"></div>
            <div class="col-auto"><button class="btn btn-outline-primary">Поиск</button></div>
        </form>
    </div></div>
    <div class="card mb-3"><div class="card-body">
        <form class="row g-2" method="post">
            <input type="hidden" name="action" value="create">
            <?php foreach ($fields as $field => $label): ?>
                <div class="col-md-3"><input class="form-control" name="<?= h($field) ?>" placeholder="<?= h($label) ?>" required></div>
            <?php endforeach; ?>
            <div class="col-md-2"><button class="btn btn-primary w-100">Добавить</button></div>
        </form>
    </div></div>
    <div class="card"><div class="card-body">
        <table class="table table-hover align-middle"><thead><tr><th>ID</th>
            <?php foreach ($fields as $label): ?><th><?= h($label) ?></th><?php endforeach; ?>
            <th>Действия</th></tr></thead><tbody>
            <?php foreach ($rows as $row): ?><tr><form method="post">
                <td><?= (int) $row['id'] ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"></td>
                <?php foreach ($fields as $field => $label): ?><td><input class="form-control" name="<?= h($field) ?>" value="<?= h($row[$field]) ?>" required></td><?php endforeach; ?>
                <td>
                    <button class="btn btn-sm btn-success" name="action" value="update">Сохранить</button>
                    <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm('Удалить запись?')">Удалить</button>
                </td>
            </form></tr><?php endforeach; ?>
        </tbody></table>
    </div></div>
    <?php
    render_footer();
}

function module_batches(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            db()->prepare('INSERT INTO batches (medicine_id,batch_number,expiration_date,quantity,purchase_price) VALUES (:medicine_id,:batch_number,:expiration_date,:quantity,:purchase_price)')
                ->execute([
                    'medicine_id' => (int) $_POST['medicine_id'],
                    'batch_number' => trim($_POST['batch_number']),
                    'expiration_date' => $_POST['expiration_date'],
                    'quantity' => (int) $_POST['quantity'],
                    'purchase_price' => (float) $_POST['purchase_price'],
                ]);
            flash('Партия добавлена.');
        } elseif ($action === 'update') {
            db()->prepare('UPDATE batches SET medicine_id=:medicine_id,batch_number=:batch_number,expiration_date=:expiration_date,quantity=:quantity,purchase_price=:purchase_price WHERE id=:id')
                ->execute([
                    'id' => (int) $_POST['id'],
                    'medicine_id' => (int) $_POST['medicine_id'],
                    'batch_number' => trim($_POST['batch_number']),
                    'expiration_date' => $_POST['expiration_date'],
                    'quantity' => (int) $_POST['quantity'],
                    'purchase_price' => (float) $_POST['purchase_price'],
                ]);
            flash('Партия обновлена.');
        } elseif ($action === 'delete') {
            db()->prepare('DELETE FROM batches WHERE id=:id')->execute(['id' => (int) $_POST['id']]);
            flash('Партия удалена.');
        }
        redirect('index.php?page=batches');
    }

    $q = trim($_GET['q'] ?? '');
    $medicines = db()->query('SELECT id,name FROM medicines ORDER BY name')->fetchAll();
    $stmt = db()->prepare('SELECT b.*,m.name AS medicine_name FROM batches b JOIN medicines m ON m.id=b.medicine_id WHERE b.batch_number LIKE :q OR m.name LIKE :q ORDER BY b.id DESC');
    $stmt->execute(['q' => '%' . $q . '%']);
    $rows = $stmt->fetchAll();

    render_header('Партии');
    include __DIR__ . '/views_batches.php';
    render_footer();
}

function module_receipts(): void
{
    $pdo = db();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $pdo->beginTransaction();
        try {
            if ($action === 'create') {
                $qty = (int) $_POST['quantity'];
                $pdo->prepare('INSERT INTO receipts (supplier_id,batch_id,quantity,receipt_date) VALUES (:supplier_id,:batch_id,:quantity,:receipt_date)')->execute([
                    'supplier_id' => (int) $_POST['supplier_id'],
                    'batch_id' => (int) $_POST['batch_id'],
                    'quantity' => $qty,
                    'receipt_date' => $_POST['receipt_date'],
                ]);
                $pdo->prepare('UPDATE batches SET quantity=quantity+:q WHERE id=:id')->execute(['q' => $qty, 'id' => (int) $_POST['batch_id']]);
                flash('Поступление добавлено и остаток обновлён.');
            } elseif ($action === 'update') {
                $id = (int) $_POST['id'];
                $oldStmt = $pdo->prepare('SELECT batch_id,quantity FROM receipts WHERE id=:id');
                $oldStmt->execute(['id' => $id]);
                $old = $oldStmt->fetch();
                if ($old) {
                    $newBatch = (int) $_POST['batch_id'];
                    $newQty = (int) $_POST['quantity'];
                    $pdo->prepare('UPDATE batches SET quantity=quantity-:q WHERE id=:id')->execute(['q' => (int) $old['quantity'], 'id' => (int) $old['batch_id']]);
                    $pdo->prepare('UPDATE batches SET quantity=quantity+:q WHERE id=:id')->execute(['q' => $newQty, 'id' => $newBatch]);
                    $pdo->prepare('UPDATE receipts SET supplier_id=:supplier_id,batch_id=:batch_id,quantity=:quantity,receipt_date=:receipt_date WHERE id=:id')->execute([
                        'id' => $id,
                        'supplier_id' => (int) $_POST['supplier_id'],
                        'batch_id' => $newBatch,
                        'quantity' => $newQty,
                        'receipt_date' => $_POST['receipt_date'],
                    ]);
                }
                flash('Поступление обновлено.');
            } elseif ($action === 'delete') {
                $r = $pdo->prepare('SELECT batch_id, quantity FROM receipts WHERE id=:id');
                $r->execute(['id' => (int) $_POST['id']]);
                $old = $r->fetch();
                if ($old) {
                    $pdo->prepare('DELETE FROM receipts WHERE id=:id')->execute(['id' => (int) $_POST['id']]);
                    $pdo->prepare('UPDATE batches SET quantity=GREATEST(quantity-:q,0) WHERE id=:id')->execute(['q' => (int) $old['quantity'], 'id' => (int) $old['batch_id']]);
                }
                flash('Поступление удалено.');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('Ошибка: ' . $e->getMessage());
        }
        redirect('index.php?page=receipts');
    }

    $q = trim($_GET['q'] ?? '');
    $suppliers = $pdo->query('SELECT id,name FROM suppliers ORDER BY name')->fetchAll();
    $batches = $pdo->query('SELECT b.id,b.batch_number,b.quantity,m.name FROM batches b JOIN medicines m ON m.id=b.medicine_id ORDER BY b.id DESC')->fetchAll();
    $stmt = $pdo->prepare('SELECT r.*,s.name AS supplier_name,b.batch_number,m.name AS medicine_name FROM receipts r JOIN suppliers s ON s.id=r.supplier_id JOIN batches b ON b.id=r.batch_id JOIN medicines m ON m.id=b.medicine_id WHERE s.name LIKE :q OR b.batch_number LIKE :q OR m.name LIKE :q ORDER BY r.id DESC');
    $stmt->execute(['q' => '%' . $q . '%']);
    $rows = $stmt->fetchAll();

    render_header('Поступления');
    include __DIR__ . '/views_receipts.php';
    render_footer();
}

function module_writeoffs(): void
{
    $pdo = db();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $pdo->beginTransaction();
        try {
            if ($action === 'create') {
                $batchId = (int) $_POST['batch_id'];
                $qty = (int) $_POST['quantity'];
                $stmt = $pdo->prepare('SELECT quantity FROM batches WHERE id=:id');
                $stmt->execute(['id' => $batchId]);
                $available = (int) $stmt->fetchColumn();
                if ($qty > $available) {
                    throw new RuntimeException('Недостаточный остаток в партии.');
                }
                $pdo->prepare('INSERT INTO write_offs (batch_id,department_id,quantity,writeoff_date,reason) VALUES (:batch_id,:department_id,:quantity,:writeoff_date,:reason)')
                    ->execute([
                        'batch_id' => $batchId,
                        'department_id' => (int) $_POST['department_id'],
                        'quantity' => $qty,
                        'writeoff_date' => $_POST['writeoff_date'],
                        'reason' => trim($_POST['reason']),
                    ]);
                $pdo->prepare('UPDATE batches SET quantity=quantity-:q WHERE id=:id')->execute(['q' => $qty, 'id' => $batchId]);
                flash('Списание выполнено.');
            } elseif ($action === 'update') {
                $id = (int) $_POST['id'];
                $oldStmt = $pdo->prepare('SELECT batch_id,quantity FROM write_offs WHERE id=:id');
                $oldStmt->execute(['id' => $id]);
                $old = $oldStmt->fetch();
                if ($old) {
                    $newBatch = (int) $_POST['batch_id'];
                    $newQty = (int) $_POST['quantity'];
                    $pdo->prepare('UPDATE batches SET quantity=quantity+:q WHERE id=:id')->execute(['q' => (int) $old['quantity'], 'id' => (int) $old['batch_id']]);
                    $check = $pdo->prepare('SELECT quantity FROM batches WHERE id=:id');
                    $check->execute(['id' => $newBatch]);
                    $avail = (int) $check->fetchColumn();
                    if ($newQty > $avail) {
                        throw new RuntimeException('Недостаточный остаток в партии.');
                    }
                    $pdo->prepare('UPDATE batches SET quantity=quantity-:q WHERE id=:id')->execute(['q' => $newQty, 'id' => $newBatch]);
                    $pdo->prepare('UPDATE write_offs SET batch_id=:batch_id,department_id=:department_id,quantity=:quantity,writeoff_date=:writeoff_date,reason=:reason WHERE id=:id')->execute([
                        'id' => $id,
                        'batch_id' => $newBatch,
                        'department_id' => (int) $_POST['department_id'],
                        'quantity' => $newQty,
                        'writeoff_date' => $_POST['writeoff_date'],
                        'reason' => trim($_POST['reason']),
                    ]);
                }
                flash('Списание обновлено.');
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare('SELECT batch_id,quantity FROM write_offs WHERE id=:id');
                $stmt->execute(['id' => (int) $_POST['id']]);
                $old = $stmt->fetch();
                if ($old) {
                    $pdo->prepare('DELETE FROM write_offs WHERE id=:id')->execute(['id' => (int) $_POST['id']]);
                    $pdo->prepare('UPDATE batches SET quantity=quantity+:q WHERE id=:id')->execute(['q' => (int) $old['quantity'], 'id' => (int) $old['batch_id']]);
                }
                flash('Списание удалено.');
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('Ошибка: ' . $e->getMessage());
        }
        redirect('index.php?page=write_offs');
    }

    $q = trim($_GET['q'] ?? '');
    $departments = $pdo->query('SELECT id,name FROM departments ORDER BY name')->fetchAll();
    $batches = $pdo->query('SELECT b.id,b.batch_number,b.quantity,b.expiration_date,m.name FROM batches b JOIN medicines m ON m.id=b.medicine_id ORDER BY b.id DESC')->fetchAll();
    $stmt = $pdo->prepare('SELECT w.*,d.name AS department_name,b.batch_number,m.name AS medicine_name FROM write_offs w JOIN departments d ON d.id=w.department_id JOIN batches b ON b.id=w.batch_id JOIN medicines m ON m.id=b.medicine_id WHERE d.name LIKE :q OR m.name LIKE :q OR b.batch_number LIKE :q OR w.reason LIKE :q ORDER BY w.id DESC');
    $stmt->execute(['q' => '%' . $q . '%']);
    $rows = $stmt->fetchAll();

    render_header('Списания');
    include __DIR__ . '/views_writeoffs.php';
    render_footer();
}

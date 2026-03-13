<h2 class="mb-3">Поступления</h2>
<div class="card mb-3"><div class="card-body">
    <form class="row g-2" method="get"><input type="hidden" name="page" value="receipts"><div class="col-md-5"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Поиск"></div><div class="col-auto"><button class="btn btn-outline-primary">Поиск</button></div></form>
</div></div>
<div class="card mb-3"><div class="card-body">
    <h5>Добавить поступление</h5>
    <form class="row g-2" method="post">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><select class="form-select" name="supplier_id" required><?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= h($s['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select batch-select" name="batch_id" required><?php foreach ($batches as $b): ?><option value="<?= (int)$b['id'] ?>" data-info="<?= h($b['name'].' | '.$b['batch_number'].' | остаток '.$b['quantity']) ?>"><?= h($b['name'].' / '.$b['batch_number']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" type="number" min="1" name="quantity" placeholder="Кол-во" required></div>
        <div class="col-md-2"><input class="form-control" type="date" name="receipt_date" value="<?= date('Y-m-d') ?>" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Добавить</button></div>
        <div class="col-12 small text-muted batch-info"></div>
    </form>
</div></div>
<div class="card"><div class="card-body"><table class="table table-hover"><thead><tr><th>ID</th><th>Поставщик</th><th>Партия</th><th>Кол-во</th><th>Дата</th><th>Действия</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><form method="post"><td><?= (int)$row['id'] ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"></td>
<td><select class="form-select" name="supplier_id"><?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $row['supplier_id']==$s['id']?'selected':'' ?>><?= h($s['name']) ?></option><?php endforeach; ?></select></td>
<td><select class="form-select" name="batch_id"><?php foreach ($batches as $b): ?><option value="<?= (int)$b['id'] ?>" <?= $row['batch_id']==$b['id']?'selected':'' ?>><?= h($b['name'].' / '.$b['batch_number']) ?></option><?php endforeach; ?></select></td>
<td><input class="form-control" type="number" min="1" name="quantity" value="<?= (int)$row['quantity'] ?>"></td>
<td><input class="form-control" type="date" name="receipt_date" value="<?= h($row['receipt_date']) ?>"></td>
<td><button class="btn btn-sm btn-success" name="action" value="update">Сохранить</button> <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm('Удалить поступление?')">Удалить</button></td></form></tr><?php endforeach; ?>
</tbody></table></div></div>

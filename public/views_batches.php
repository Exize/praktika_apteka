<h2 class="mb-3">Партии препаратов</h2>
<div class="card mb-3"><div class="card-body">
    <form class="row g-2" method="get">
        <input type="hidden" name="page" value="batches">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="Поиск по партии или препарату"></div>
        <div class="col-auto"><button class="btn btn-outline-primary">Поиск</button></div>
    </form>
</div></div>
<div class="card mb-3"><div class="card-body">
    <h5>Добавить партию</h5>
    <form class="row g-2" method="post">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><select class="form-select" name="medicine_id" required><?php foreach ($medicines as $m): ?><option value="<?= (int)$m['id'] ?>"><?= h($m['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" name="batch_number" placeholder="Номер партии" required></div>
        <div class="col-md-2"><input class="form-control" type="date" name="expiration_date" required></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" name="quantity" placeholder="Кол-во" required></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" min="0" name="purchase_price" placeholder="Цена" required></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">+</button></div>
    </form>
</div></div>
<div class="card"><div class="card-body"><table class="table table-hover align-middle"><thead><tr><th>ID</th><th>Препарат</th><th>Партия</th><th>Срок</th><th>Остаток</th><th>Цена</th><th>Действия</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><form method="post"><td><?= (int)$row['id'] ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"></td>
<td><select class="form-select" name="medicine_id"><?php foreach ($medicines as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $row['medicine_id']==$m['id']?'selected':'' ?>><?= h($m['name']) ?></option><?php endforeach; ?></select></td>
<td><input class="form-control" name="batch_number" value="<?= h($row['batch_number']) ?>" required></td>
<td><input class="form-control" type="date" name="expiration_date" value="<?= h($row['expiration_date']) ?>" required></td>
<td><input class="form-control" type="number" min="0" name="quantity" value="<?= (int)$row['quantity'] ?>" required></td>
<td><input class="form-control" type="number" step="0.01" min="0" name="purchase_price" value="<?= h($row['purchase_price']) ?>" required></td>
<td><button class="btn btn-sm btn-success" name="action" value="update">Сохранить</button> <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm('Удалить партию?')">Удалить</button></td></form></tr><?php endforeach; ?>
</tbody></table></div></div>

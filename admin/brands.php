<?php
require_once __DIR__ . '/../db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name === '') flash('error', 'Введите название');
    elseif ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE brands SET name=? WHERE id=?");
        $stmt->bind_param('si', $name, $id);
        try_exec($stmt, 'Бренд обновлён', 'Не удалось обновить бренд');
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        try_exec($stmt, 'Бренд добавлен', 'Не удалось добавить бренд');
        $stmt->close();
    }
    header('Location: brands.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cnt = (int)fetch_one($mysqli, 'SELECT COUNT(*) c FROM products WHERE brand_id=?', 'i', [$id])['c'];
    if ($cnt > 0) {
        flash('error', "Нельзя удалить: к бренду привязано $cnt товар(ов).");
    } else {
        $stmt = $mysqli->prepare("DELETE FROM brands WHERE id=?");
        $stmt->bind_param('i', $id);
        try_exec($stmt, 'Бренд удалён', 'Не удалось удалить бренд');
        $stmt->close();
    }
    header('Location: brands.php');
    exit;
}

$edit = isset($_GET['edit']) ? fetch_one($mysqli, 'SELECT * FROM brands WHERE id=?', 'i', [(int)$_GET['edit']]) : null;
$bs = $mysqli->query("SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id=b.id) AS cnt FROM brands b ORDER BY b.name")->fetch_all(MYSQLI_ASSOC);

admin_head('Бренды');
?>
<h1>Бренды</h1>
<div class="grid2">
  <section class="card">
    <h2><?= $edit ? 'Редактирование' : 'Добавить бренд' ?></h2>
    <form method="post" class="form-stack">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <label>Название<input type="text" name="name" required value="<?= h($edit['name'] ?? '') ?>"></label>
      <button class="btn btn--primary"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
      <?php if ($edit): ?><a href="brands.php" class="btn btn--ghost">Отмена</a><?php endif; ?>
    </form>
  </section>
  <section class="card">
    <h2>Список</h2>
    <table class="tbl">
      <thead><tr><th>#</th><th>Название</th><th>Товаров</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($bs as $b): ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= h($b['name']) ?></td>
            <td><?= (int)$b['cnt'] ?></td>
            <td class="actions">
              <a href="brands.php?edit=<?= (int)$b['id'] ?>" class="btn btn--small">Изм.</a>
              <a href="brands.php?delete=<?= (int)$b['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить?')">×</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
<?php admin_foot(); ?>

<?php
require_once __DIR__ . '/../db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    if ($name === '' || $slug === '') { flash('error', 'Заполните название и слаг'); }
    elseif ($id > 0) {
        $stmt = $mysqli->prepare("UPDATE categories SET name=?, slug=?, icon=?, sort_order=? WHERE id=?");
        $stmt->bind_param('sssii', $name, $slug, $icon, $sort, $id);
        try_exec($stmt, 'Категория обновлена', 'Не удалось обновить категорию');
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO categories (name, slug, icon, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $name, $slug, $icon, $sort);
        try_exec($stmt, 'Категория добавлена', 'Не удалось добавить категорию');
        $stmt->close();
    }
    header('Location: categories.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cnt = (int)fetch_one($mysqli, 'SELECT COUNT(*) c FROM products WHERE category_id=?', 'i', [$id])['c'];
    if ($cnt > 0) {
        flash('error', "Нельзя удалить: в категории $cnt товар(ов).");
    } else {
        $stmt = $mysqli->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param('i', $id);
        try_exec($stmt, 'Категория удалена', 'Не удалось удалить категорию');
        $stmt->close();
    }
    header('Location: categories.php');
    exit;
}

$edit = isset($_GET['edit']) ? fetch_one($mysqli, 'SELECT * FROM categories WHERE id=?', 'i', [(int)$_GET['edit']]) : null;
$cs = $mysqli->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id) AS cnt FROM categories c ORDER BY c.sort_order, c.name")->fetch_all(MYSQLI_ASSOC);

admin_head('Категории');
?>
<h1>Категории</h1>
<div class="grid2">
  <section class="card">
    <h2><?= $edit ? 'Редактирование' : 'Добавить категорию' ?></h2>
    <form method="post" class="form-stack">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <label>Название<input type="text" name="name" required value="<?= h($edit['name'] ?? '') ?>"></label>
      <label>Слаг (латиницей)<input type="text" name="slug" required value="<?= h($edit['slug'] ?? '') ?>"></label>
      <label>Иконка (эмодзи)<input type="text" name="icon" maxlength="4" value="<?= h($edit['icon'] ?? '') ?>" placeholder="📦"></label>
      <label>Порядок<input type="number" name="sort_order" value="<?= (int)($edit['sort_order'] ?? 0) ?>"></label>
      <button class="btn btn--primary"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
      <?php if ($edit): ?><a href="categories.php" class="btn btn--ghost">Отмена</a><?php endif; ?>
    </form>
  </section>
  <section class="card">
    <h2>Список</h2>
    <table class="tbl">
      <thead><tr><th>#</th><th></th><th>Название</th><th>Слаг</th><th>Товаров</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($cs as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td style="font-size:24px"><?= h($c['icon'] ?: '🔹') ?></td>
            <td><?= h($c['name']) ?></td>
            <td class="muted"><?= h($c['slug']) ?></td>
            <td><?= (int)$c['cnt'] ?></td>
            <td class="actions">
              <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn btn--small">Изм.</a>
              <a href="categories.php?delete=<?= (int)$c['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить?')">×</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
<?php admin_foot(); ?>

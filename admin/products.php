<?php
require_once __DIR__ . '/../db.php';
require_admin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cnt = (int)fetch_one($mysqli, 'SELECT COUNT(*) c FROM order_items WHERE product_id=?', 'i', [$id])['c'];
    if ($cnt > 0) {
        flash('error', "Нельзя удалить: товар используется в $cnt заказе(ах).");
    } else {
        $mysqli->query("DELETE FROM reviews WHERE product_id=$id");
        $stmt = $mysqli->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param('i', $id);
        try_exec($stmt, 'Товар удалён', 'Не удалось удалить товар');
        $stmt->close();
    }
    header('Location: products.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['category'] ?? 0);
$where = '1=1'; $types = ''; $params = [];
if ($q !== '') { $where .= " AND (p.name LIKE ? OR p.model LIKE ?)"; $like = '%'.$q.'%'; $types .= 'ss'; $params[] = $like; $params[] = $like; }
if ($cat) { $where .= " AND p.category_id=?"; $types .= 'i'; $params[] = $cat; }

$products = fetch_all($mysqli,
    "SELECT p.*, c.name AS category, b.name AS brand
     FROM products p JOIN categories c ON c.id=p.category_id JOIN brands b ON b.id=p.brand_id
     WHERE $where ORDER BY p.id DESC", $types, $params);

admin_head('Товары');
?>
<div class="head">
  <h1>Товары <span class="muted">(<?= count($products) ?>)</span></h1>
  <a href="product_edit.php" class="btn btn--primary">+ Добавить товар</a>
</div>
<form class="toolbar" method="get">
  <input type="text" name="q" value="<?= h($q) ?>" placeholder="Поиск по названию или модели">
  <select name="category">
    <option value="0">Все категории</option>
    <?php foreach (cats($mysqli) as $c): ?><option value="<?= (int)$c['id'] ?>"<?= $cat === (int)$c['id'] ? ' selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
  </select>
  <button class="btn">Найти</button>
  <a href="products.php" class="btn btn--ghost">Сброс</a>
</form>
<table class="tbl tbl--wide">
  <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Категория</th><th>Бренд</th><th>Цена</th><th>Остаток</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?= (int)$p['id'] ?></td>
        <td><img class="thumb" src="../image.php?id=<?= (int)$p['id'] ?>" alt=""></td>
        <td><b><?= h($p['name']) ?></b><div class="muted"><?= h($p['model']) ?></div></td>
        <td><?= h($p['category']) ?></td>
        <td><?= h($p['brand']) ?></td>
        <td><?= money($p['price']) ?></td>
        <td><?= (int)$p['stock'] ?></td>
        <td class="actions">
          <a href="product_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn--small">Изм.</a>
          <a href="products.php?delete=<?= (int)$p['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить товар?')">×</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot(); ?>

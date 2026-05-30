<?php
require_once __DIR__ . '/../db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$p = $is_edit
    ? fetch_one($mysqli, "SELECT * FROM products WHERE id=?", 'i', [$id])
    : ['id'=>0,'category_id'=>0,'brand_id'=>0,'name'=>'','model'=>'','price'=>0,'memory'=>'','screen'=>'','color'=>'','description'=>'','stock'=>0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = (int)$_POST['category_id'];
    $br = (int)$_POST['brand_id'];
    $name = trim($_POST['name']);
    $model = trim($_POST['model']);
    $price = (float)$_POST['price'];
    $mem = trim($_POST['memory']);
    $scr = trim($_POST['screen']);
    $col = trim($_POST['color']);
    $desc = trim($_POST['description']);
    $stock = (int)$_POST['stock'];

    $blob = null; $mime = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $mime = mime_content_type($tmp);
        if (in_array($mime, ['image/jpeg','image/png','image/webp','image/gif'])) {
            $blob = file_get_contents($tmp);
        } else {
            flash('error', 'Поддерживаются JPG, PNG, WEBP, GIF');
            header('Location: product_edit.php' . ($is_edit ? '?id='.$id : ''));
            exit;
        }
    }

    if ($is_edit) {
        if ($blob !== null) {
            $stmt = $mysqli->prepare("UPDATE products SET category_id=?, brand_id=?, name=?, model=?, price=?, memory=?, screen=?, color=?, description=?, stock=?, image=?, image_mime=? WHERE id=?");
            $null = null;
            $stmt->bind_param('iissdssssibsi', $cat, $br, $name, $model, $price, $mem, $scr, $col, $desc, $stock, $null, $mime, $id);
            $stmt->send_long_data(10, $blob);
        } else {
            $stmt = $mysqli->prepare("UPDATE products SET category_id=?, brand_id=?, name=?, model=?, price=?, memory=?, screen=?, color=?, description=?, stock=? WHERE id=?");
            $stmt->bind_param('iissdssssii', $cat, $br, $name, $model, $price, $mem, $scr, $col, $desc, $stock, $id);
        }
        try_exec($stmt, 'Товар обновлён', 'Не удалось обновить товар');
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO products (category_id, brand_id, name, model, price, memory, screen, color, description, stock, image, image_mime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $null = null;
        $stmt->bind_param('iissdssssibs', $cat, $br, $name, $model, $price, $mem, $scr, $col, $desc, $stock, $null, $mime);
        if ($blob !== null) $stmt->send_long_data(10, $blob);
        if (try_exec($stmt, 'Товар создан', 'Не удалось создать товар')) {
            $id = $stmt->insert_id;
        }
        $stmt->close();
    }
    header('Location: product_edit.php?id=' . $id);
    exit;
}

$brands = $mysqli->query("SELECT id, name FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
admin_head($is_edit ? 'Редактирование товара' : 'Новый товар');
?>
<div class="head">
  <h1><?= $is_edit ? 'Товар #'.(int)$p['id'] : 'Новый товар' ?></h1>
  <a href="products.php" class="btn btn--ghost">← К списку</a>
</div>
<form method="post" enctype="multipart/form-data" class="form-grid">
  <div class="form-grid__main">
    <label>Название<input type="text" name="name" required value="<?= h($p['name']) ?>"></label>
    <label>Модель<input type="text" name="model" required value="<?= h($p['model']) ?>"></label>
    <div class="row3">
      <label>Цена, ₽<input type="number" step="0.01" name="price" required value="<?= h($p['price']) ?>"></label>
      <label>Остаток, шт.<input type="number" name="stock" value="<?= h($p['stock']) ?>"></label>
      <label>Память<input type="text" name="memory" value="<?= h($p['memory']) ?>"></label>
    </div>
    <div class="row3">
      <label>Экран<input type="text" name="screen" value="<?= h($p['screen']) ?>"></label>
      <label>Цвет<input type="text" name="color" value="<?= h($p['color']) ?>"></label>
      <label>&nbsp;</label>
    </div>
    <label>Категория<select name="category_id" required>
      <?php foreach (cats($mysqli) as $c): ?><option value="<?= (int)$c['id'] ?>"<?= (int)$p['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Бренд<select name="brand_id" required>
      <?php foreach ($brands as $b): ?><option value="<?= (int)$b['id'] ?>"<?= (int)$p['brand_id'] === (int)$b['id'] ? ' selected' : '' ?>><?= h($b['name']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Описание<textarea name="description" rows="6" required><?= h($p['description']) ?></textarea></label>
  </div>
  <aside class="form-grid__aside">
    <label class="img-up">
      <div class="img-up__prev"><?= $is_edit ? '<img src="../image.php?id='.(int)$p['id'].'" alt="">' : '<span>Загрузить изображение</span>' ?></div>
      <input type="file" name="image" accept="image/*">
      <small>JPG/PNG/WEBP, до 8 МБ</small>
    </label>
    <button type="submit" class="btn btn--primary btn--full">Сохранить</button>
    <?php if ($is_edit): ?>
      <a href="../product.php?id=<?= (int)$p['id'] ?>" target="_blank" class="btn btn--ghost btn--full">Открыть на сайте</a>
      <a href="products.php?delete=<?= (int)$p['id'] ?>" class="btn btn--danger btn--full" onclick="return confirm('Удалить товар?')">Удалить</a>
    <?php endif; ?>
  </aside>
</form>
<?php admin_foot(); ?>

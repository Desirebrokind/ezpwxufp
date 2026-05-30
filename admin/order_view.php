<?php
require_once __DIR__ . '/../db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$o = fetch_one($mysqli,
    "SELECT o.*, u.full_name AS user_name, u.email AS user_email, u.login AS user_login
     FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?", 'i', [$id]);
if (!$o) { flash('error', 'Заказ не найден'); header('Location: orders.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status']);
    $comment = trim($_POST['comment']);
    $stmt = $mysqli->prepare("UPDATE orders SET status=?, comment=? WHERE id=?");
    $stmt->bind_param('ssi', $status, $comment, $id);
    try_exec($stmt, 'Статус заказа обновлён', 'Не удалось обновить заказ');
    $stmt->close();
    header('Location: order_view.php?id=' . $id);
    exit;
}

$items = fetch_all($mysqli,
    "SELECT oi.*, p.name, p.id AS pid FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?", 'i', [$id]);
$statuses = ['Новый','Подтверждён','В сборке','Отправлен','Доставлен','Отменён'];

admin_head('Заказ #' . (int)$o['id']);
?>
<div class="head"><h1>Заказ #<?= (int)$o['id'] ?></h1><a href="orders.php" class="btn btn--ghost">← К списку</a></div>
<div class="grid2">
  <section class="card">
    <h2>Состав заказа</h2>
    <table class="tbl">
      <thead><tr><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th></tr></thead>
      <tbody>
        <?php $sum = 0; foreach ($items as $it): $sub = $it['price']*$it['quantity']; $sum += $sub; ?>
          <tr>
            <td><a href="product_edit.php?id=<?= (int)$it['pid'] ?>"><?= h($it['name']) ?></a></td>
            <td><?= (int)$it['quantity'] ?></td>
            <td><?= money($it['price']) ?></td>
            <td><b><?= money($sub) ?></b></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot><tr><td colspan="3"><b>Итого</b></td><td><b><?= money($sum) ?></b></td></tr></tfoot>
    </table>
  </section>
  <section class="card">
    <h2>Покупатель</h2>
    <dl class="dl">
      <dt>Аккаунт</dt><dd><?= h($o['user_login']) ?> (<?= h($o['user_email']) ?>)</dd>
      <dt>ФИО</dt><dd><?= h($o['customer_name']) ?></dd>
      <dt>Телефон</dt><dd><?= h($o['customer_phone']) ?></dd>
      <dt>Адрес</dt><dd><?= h($o['customer_address']) ?></dd>
      <dt>Создан</dt><dd><?= h(date('d.m.Y H:i', strtotime($o['created_at']))) ?></dd>
    </dl>
    <h2>Управление</h2>
    <form method="post" class="form-stack">
      <label>Статус<select name="status">
        <?php foreach ($statuses as $s): ?><option value="<?= h($s) ?>"<?= $o['status'] === $s ? ' selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?>
      </select></label>
      <label>Внутренний комментарий<textarea name="comment" rows="3"><?= h($o['comment']) ?></textarea></label>
      <button class="btn btn--primary">Сохранить</button>
    </form>
  </section>
</div>
<?php admin_foot(); ?>

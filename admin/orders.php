<?php
require_once __DIR__ . '/../db.php';
require_admin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $mysqli->query("DELETE FROM order_items WHERE order_id=$id");
    $stmt = $mysqli->prepare("DELETE FROM orders WHERE id=?");
    $stmt->bind_param('i', $id);
    try_exec($stmt, 'Заказ удалён', 'Не удалось удалить заказ');
    $stmt->close();
    header('Location: orders.php');
    exit;
}

$status = $_GET['status'] ?? '';
$where = '1=1'; $types = ''; $params = [];
if ($status !== '') { $where .= ' AND o.status=?'; $types .= 's'; $params[] = $status; }

$orders = fetch_all($mysqli,
    "SELECT o.*, u.full_name, u.email, (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) AS cnt
     FROM orders o JOIN users u ON u.id=o.user_id WHERE $where ORDER BY o.created_at DESC", $types, $params);

$statuses = ['Новый','Подтверждён','В сборке','Отправлен','Доставлен','Отменён'];

admin_head('Заказы');
?>
<h1>Заказы <span class="muted">(<?= count($orders) ?>)</span></h1>
<form class="toolbar" method="get">
  <select name="status">
    <option value="">Все статусы</option>
    <?php foreach ($statuses as $s): ?><option value="<?= h($s) ?>"<?= $status === $s ? ' selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?>
  </select>
  <button class="btn">Применить</button>
  <a href="orders.php" class="btn btn--ghost">Сброс</a>
</form>
<table class="tbl tbl--wide">
  <thead><tr><th>№</th><th>Дата</th><th>Покупатель</th><th>Товаров</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><a href="order_view.php?id=<?= (int)$o['id'] ?>"><b>#<?= (int)$o['id'] ?></b></a></td>
        <td><?= h(date('d.m.Y H:i', strtotime($o['created_at']))) ?></td>
        <td><div><?= h($o['customer_name']) ?></div><div class="muted"><?= h($o['email']) ?></div></td>
        <td><?= (int)$o['cnt'] ?></td>
        <td><b><?= money($o['total']) ?></b></td>
        <td><span class="status status--<?= h(str_replace(' ','-',mb_strtolower($o['status']))) ?>"><?= h($o['status']) ?></span></td>
        <td class="actions">
          <a href="order_view.php?id=<?= (int)$o['id'] ?>" class="btn btn--small">Открыть</a>
          <a href="orders.php?delete=<?= (int)$o['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить заказ?')">×</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot(); ?>

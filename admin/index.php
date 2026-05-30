<?php
require_once __DIR__ . '/../db.php';
require_admin();

$s = [
    'products' => (int)$mysqli->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'],
    'orders' => (int)$mysqli->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'],
    'users' => (int)$mysqli->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
    'revenue' => (float)$mysqli->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status!='Отменён'")->fetch_assoc()['s'],
    'reviews' => (int)$mysqli->query("SELECT COUNT(*) c FROM reviews")->fetch_assoc()['c'],
    'categories' => (int)$mysqli->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'],
];
$recent = $mysqli->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$top = $mysqli->query("SELECT p.id, p.name, SUM(oi.quantity) qty, SUM(oi.price*oi.quantity) rev FROM order_items oi JOIN products p ON p.id=oi.product_id GROUP BY p.id ORDER BY qty DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

admin_head('Дашборд');
?>
<h1>Дашборд</h1>
<div class="kpis">
  <div class="kpi"><span>Товаров</span><b><?= $s['products'] ?></b></div>
  <div class="kpi"><span>Заказов</span><b><?= $s['orders'] ?></b></div>
  <div class="kpi"><span>Пользователей</span><b><?= $s['users'] ?></b></div>
  <div class="kpi kpi--accent"><span>Оборот</span><b><?= money($s['revenue']) ?></b></div>
  <div class="kpi"><span>Категорий</span><b><?= $s['categories'] ?></b></div>
  <div class="kpi"><span>Отзывов</span><b><?= $s['reviews'] ?></b></div>
</div>
<div class="grid2">
  <section class="card">
    <h2>Последние заказы</h2>
    <table class="tbl">
      <thead><tr><th>№</th><th>Покупатель</th><th>Статус</th><th>Сумма</th><th>Дата</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $o): ?>
        <tr>
          <td><a href="order_view.php?id=<?= (int)$o['id'] ?>">#<?= (int)$o['id'] ?></a></td>
          <td><?= h($o['full_name']) ?></td>
          <td><span class="status status--<?= h(str_replace(' ','-',mb_strtolower($o['status']))) ?>"><?= h($o['status']) ?></span></td>
          <td><?= money($o['total']) ?></td>
          <td><?= h(date('d.m.Y', strtotime($o['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <section class="card">
    <h2>Топ товаров</h2>
    <table class="tbl">
      <thead><tr><th>Товар</th><th>Шт.</th><th>Выручка</th></tr></thead>
      <tbody>
      <?php foreach ($top as $p): ?>
        <tr>
          <td><a href="product_edit.php?id=<?= (int)$p['id'] ?>"><?= h($p['name']) ?></a></td>
          <td><?= (int)$p['qty'] ?></td>
          <td><?= money($p['rev']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
<?php admin_foot(); ?>

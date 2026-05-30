<?php
require_once __DIR__ . '/../db.php';
require_admin();

$top_sales = $mysqli->query(
    "SELECT p.id, p.name, b.name AS brand, c.name AS category,
            SUM(oi.quantity) AS qty,
            SUM(oi.price * oi.quantity) AS revenue
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id AND o.status != 'Отменён'
     JOIN products p ON p.id = oi.product_id
     JOIN brands b ON b.id = p.brand_id
     JOIN categories c ON c.id = p.category_id
     GROUP BY p.id
     ORDER BY qty DESC, revenue DESC
     LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);

$by_category = $mysqli->query(
    "SELECT c.name AS category,
            COUNT(DISTINCT p.id) AS products,
            COALESCE(SUM(oi.quantity), 0) AS sold,
            COALESCE(SUM(oi.price * oi.quantity), 0) AS revenue
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     LEFT JOIN order_items oi ON oi.product_id = p.id
     LEFT JOIN orders o ON o.id = oi.order_id AND o.status != 'Отменён'
     GROUP BY c.id
     ORDER BY revenue DESC"
)->fetch_all(MYSQLI_ASSOC);

$by_month = $mysqli->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
            COUNT(*) AS orders,
            SUM(total) AS revenue,
            AVG(total) AS avg_check
     FROM orders
     WHERE status != 'Отменён'
     GROUP BY ym
     ORDER BY ym DESC
     LIMIT 12"
)->fetch_all(MYSQLI_ASSOC);

$low_stock = $mysqli->query(
    "SELECT p.id, p.name, p.model, p.stock, b.name AS brand, c.name AS category
     FROM products p
     JOIN brands b ON b.id = p.brand_id
     JOIN categories c ON c.id = p.category_id
     WHERE p.stock <= 5
     ORDER BY p.stock ASC, p.name
     LIMIT 20"
)->fetch_all(MYSQLI_ASSOC);

$top_customers = $mysqli->query(
    "SELECT u.id, u.full_name, u.login, u.email,
            COUNT(o.id) AS orders,
            SUM(o.total) AS spent
     FROM users u
     JOIN orders o ON o.user_id = u.id AND o.status != 'Отменён'
     GROUP BY u.id
     ORDER BY spent DESC, orders DESC
     LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);

$totals = $mysqli->query(
    "SELECT
        (SELECT COUNT(*) FROM orders WHERE status != 'Отменён') AS ord_ok,
        (SELECT COUNT(*) FROM orders WHERE status = 'Отменён') AS ord_cancel,
        (SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'Отменён') AS rev,
        (SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status != 'Отменён') AS items_sold,
        (SELECT COUNT(DISTINCT user_id) FROM orders WHERE status != 'Отменён') AS buyers,
        (SELECT COALESCE(AVG(total),0) FROM orders WHERE status != 'Отменён') AS avg_check
    "
)->fetch_assoc();

$months_ru = ['','Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];

admin_head('Отчёты');
?>
<h1>Отчёты</h1>
<p class="muted">Сводная аналитика по магазину. Отменённые заказы исключены из расчёта выручки.</p>

<div class="kpis">
  <div class="kpi"><span>Выполнено заказов</span><b><?= (int)$totals['ord_ok'] ?></b></div>
  <div class="kpi"><span>Отменено</span><b><?= (int)$totals['ord_cancel'] ?></b></div>
  <div class="kpi kpi--accent"><span>Общая выручка</span><b><?= money($totals['rev']) ?></b></div>
  <div class="kpi"><span>Средний чек</span><b><?= money($totals['avg_check']) ?></b></div>
  <div class="kpi"><span>Продано штук</span><b><?= (int)$totals['items_sold'] ?></b></div>
  <div class="kpi"><span>Уникальных покупателей</span><b><?= (int)$totals['buyers'] ?></b></div>
</div>

<section class="card">
  <h2>1. ТОП-10 продаваемых товаров</h2>
  <table class="tbl tbl--wide">
    <thead><tr><th>#</th><th>Товар</th><th>Категория</th><th>Бренд</th><th>Продано, шт.</th><th>Выручка</th></tr></thead>
    <tbody>
      <?php foreach ($top_sales as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><b><?= h($r['name']) ?></b></td>
          <td><?= h($r['category']) ?></td>
          <td><?= h($r['brand']) ?></td>
          <td><?= (int)$r['qty'] ?></td>
          <td><b><?= money($r['revenue']) ?></b></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$top_sales): ?><tr><td colspan="6" class="muted">Пока нет данных о продажах.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<section class="card">
  <h2>2. Продажи по категориям</h2>
  <table class="tbl tbl--wide">
    <thead><tr><th>Категория</th><th>Товаров в каталоге</th><th>Продано, шт.</th><th>Выручка</th><th>Доля</th></tr></thead>
    <tbody>
      <?php $sum_rev = array_sum(array_column($by_category, 'revenue')); ?>
      <?php foreach ($by_category as $r): ?>
        <?php $share = $sum_rev > 0 ? round($r['revenue'] / $sum_rev * 100, 1) : 0; ?>
        <tr>
          <td><b><?= h($r['category']) ?></b></td>
          <td><?= (int)$r['products'] ?></td>
          <td><?= (int)$r['sold'] ?></td>
          <td><?= money($r['revenue']) ?></td>
          <td>
            <div class="bar"><div class="bar__fill" style="width: <?= $share ?>%"></div></div>
            <span class="muted"><?= $share ?>%</span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<section class="card">
  <h2>3. Выручка по месяцам</h2>
  <table class="tbl">
    <thead><tr><th>Период</th><th>Заказов</th><th>Выручка</th><th>Средний чек</th></tr></thead>
    <tbody>
      <?php foreach ($by_month as $r): ?>
        <?php [$y, $m] = explode('-', $r['ym']); ?>
        <tr>
          <td><b><?= h($months_ru[(int)$m]) ?> <?= h($y) ?></b></td>
          <td><?= (int)$r['orders'] ?></td>
          <td><?= money($r['revenue']) ?></td>
          <td><?= money($r['avg_check']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$by_month): ?><tr><td colspan="4" class="muted">Заказов пока нет.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<section class="card">
  <h2>4. Низкий остаток на складе (≤ 5 шт.)</h2>
  <table class="tbl tbl--wide">
    <thead><tr><th>ID</th><th>Товар</th><th>Модель</th><th>Бренд</th><th>Категория</th><th>Остаток</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($low_stock as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><b><?= h($r['name']) ?></b></td>
          <td class="muted"><?= h($r['model']) ?></td>
          <td><?= h($r['brand']) ?></td>
          <td><?= h($r['category']) ?></td>
          <td><span class="badge <?= $r['stock'] == 0 ? 'badge--err' : 'badge--warn' ?>"><?= (int)$r['stock'] ?> шт.</span></td>
          <td><a class="btn btn--small" href="product_edit.php?id=<?= (int)$r['id'] ?>">Изм.</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$low_stock): ?><tr><td colspan="7" class="muted">Все товары достаточно укомплектованы.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<section class="card">
  <h2>5. ТОП-10 покупателей</h2>
  <table class="tbl tbl--wide">
    <thead><tr><th>#</th><th>Покупатель</th><th>Логин</th><th>Email</th><th>Заказов</th><th>Потратил</th></tr></thead>
    <tbody>
      <?php foreach ($top_customers as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><b><?= h($r['full_name']) ?></b></td>
          <td><?= h($r['login']) ?></td>
          <td class="muted"><?= h($r['email']) ?></td>
          <td><?= (int)$r['orders'] ?></td>
          <td><b><?= money($r['spent']) ?></b></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$top_customers): ?><tr><td colspan="6" class="muted">Пока нет данных о покупателях.</td></tr><?php endif; ?>
    </tbody>
  </table>
</section>

<?php admin_foot(); ?>

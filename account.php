<?php
require_once __DIR__ . '/db.php';
require_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $stmt = $mysqli->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?");
    $u = uid();
    $fn = trim($_POST['full_name'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $ph = trim($_POST['phone'] ?? '');
    $ad = trim($_POST['address'] ?? '');
    $stmt->bind_param('ssssi', $fn, $em, $ph, $ad, $u);
    $stmt->execute();
    $stmt->close();
    $_SESSION['user_name'] = $fn;
    flash('success', 'Данные обновлены');
    header('Location: account.php');
    exit;
}

$user = fetch_one($mysqli, 'SELECT * FROM users WHERE id=?', 'i', [uid()]);
$orders = fetch_all($mysqli, 'SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC', 'i', [uid()]);

head('Личный кабинет');
?>
<h1 class="page-title">Личный кабинет</h1>
<div class="account">
  <section class="account__profile">
    <h2>Профиль</h2>
    <form method="post">
      <label>ФИО<input type="text" name="full_name" value="<?= h($user['full_name']) ?>"></label>
      <label>E-mail<input type="email" name="email" value="<?= h($user['email']) ?>"></label>
      <label>Телефон<input type="tel" name="phone" value="<?= h($user['phone']) ?>"></label>
      <label>Адрес<input type="text" name="address" value="<?= h($user['address']) ?>"></label>
      <button type="submit" name="save_profile" class="btn btn--primary">Сохранить</button>
    </form>
  </section>
  <section class="account__orders">
    <h2>История заказов</h2>
    <?php if (!$orders): ?>
      <div class="empty">У вас пока нет заказов.</div>
    <?php else: ?>
      <?php foreach ($orders as $o):
        $items = fetch_all($mysqli, "SELECT oi.*, p.name, p.id AS pid FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?", 'i', [(int)$o['id']]);
      ?>
        <article class="order">
          <header>
            <div><b>Заказ №<?= (int)$o['id'] ?></b><span class="muted"> от <?= h(date('d.m.Y H:i', strtotime($o['created_at']))) ?></span></div>
            <span class="status status--<?= h(str_replace(' ','-',mb_strtolower($o['status']))) ?>"><?= h($o['status']) ?></span>
          </header>
          <ul class="order__items">
            <?php foreach ($items as $it): ?>
              <li><a href="product.php?id=<?= (int)$it['pid'] ?>"><?= h($it['name']) ?></a><span>× <?= (int)$it['quantity'] ?> = <?= money($it['price'] * $it['quantity']) ?></span></li>
            <?php endforeach; ?>
          </ul>
          <footer><span>Доставка: <?= h($o['customer_address']) ?></span><b>Итого: <?= money($o['total']) ?></b></footer>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>
<?php foot(); ?>

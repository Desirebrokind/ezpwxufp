<?php
require_once __DIR__ . '/db.php';
require_user();

if (empty($_SESSION['cart'])) { flash('error', 'Корзина пуста'); header('Location: cart.php'); exit; }

$user = fetch_one($mysqli, 'SELECT * FROM users WHERE id=?', 'i', [uid()]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    if ($name === '' || $phone === '' || $address === '') {
        flash('error', 'Заполните все обязательные поля');
    } else {
        $ids = array_keys($_SESSION['cart']);
        $place = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $mysqli->prepare("SELECT id, price FROM products WHERE id IN ($place)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $total = 0;
        foreach ($rows as $r) $total += $r['price'] * (int)$_SESSION['cart'][$r['id']];

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("INSERT INTO orders (user_id, total, status, customer_name, customer_phone, customer_address, comment) VALUES (?, ?, 'Новый', ?, ?, ?, ?)");
            $u = uid();
            $stmt->bind_param('idssss', $u, $total, $name, $phone, $address, $comment);
            $stmt->execute();
            $oid = $stmt->insert_id;
            $stmt->close();

            $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($rows as $r) {
                $qty = (int)$_SESSION['cart'][$r['id']];
                $pid = (int)$r['id'];
                $price = (float)$r['price'];
                $stmt->bind_param('iiid', $oid, $pid, $qty, $price);
                $stmt->execute();
            }
            $stmt->close();
            $mysqli->commit();
            $_SESSION['cart'] = [];
            flash('success', 'Заказ №' . $oid . ' успешно оформлен!');
            header('Location: account.php');
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            flash('error', 'Ошибка оформления заказа');
        }
    }
}

$ids = array_keys($_SESSION['cart']);
$place = implode(',', array_fill(0, count($ids), '?'));
$stmt = $mysqli->prepare("SELECT id, name, price FROM products WHERE id IN ($place)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$total = 0;
foreach ($rows as $r) $total += (int)$_SESSION['cart'][$r['id']] * $r['price'];

head('Оформление заказа');
?>
<h1 class="page-title">Оформление заказа</h1>
<div class="checkout">
  <form class="checkout__form" method="post">
    <h3>Контактные данные</h3>
    <label>ФИО получателя*<input type="text" name="name" value="<?= h($user['full_name']) ?>" required></label>
    <label>Телефон*<input type="tel" name="phone" value="<?= h($user['phone']) ?>" required></label>
    <label>Адрес доставки*<input type="text" name="address" value="<?= h($user['address']) ?>" required></label>
    <label>Комментарий к заказу<textarea name="comment" placeholder="Удобное время доставки, домофон и т.п."></textarea></label>
    <h3>Способ оплаты</h3>
    <div class="checkout__pay">
      <label class="pay-opt"><input type="radio" name="pay" value="card" checked><span>Картой при получении</span></label>
      <label class="pay-opt"><input type="radio" name="pay" value="cash"><span>Наличными при получении</span></label>
      <label class="pay-opt"><input type="radio" name="pay" value="online"><span>Онлайн на сайте</span></label>
    </div>
    <button type="submit" class="btn btn--primary btn--big">Подтвердить заказ — <?= money($total) ?></button>
  </form>
  <aside class="checkout__summary">
    <h3>Ваш заказ</h3>
    <?php foreach ($rows as $r): ?>
      <div class="checkout__row"><span><?= h($r['name']) ?> × <?= (int)$_SESSION['cart'][$r['id']] ?></span><b><?= money($r['price'] * $_SESSION['cart'][$r['id']]) ?></b></div>
    <?php endforeach; ?>
    <div class="checkout__total">Итого: <?= money($total) ?></div>
  </aside>
</div>
<?php foot(); ?>

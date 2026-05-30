<?php
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);
    if ($a === 'add' && $pid > 0) {
        $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + max(1, (int)($_POST['quantity'] ?? 1));
        flash('success', 'Товар добавлен в корзину');
    } elseif ($a === 'update' && $pid > 0) {
        $qty = max(0, (int)($_POST['quantity'] ?? 0));
        if ($qty === 0) unset($_SESSION['cart'][$pid]);
        else $_SESSION['cart'][$pid] = $qty;
    } elseif ($a === 'remove' && $pid > 0) {
        unset($_SESSION['cart'][$pid]);
    } elseif ($a === 'clear') {
        $_SESSION['cart'] = [];
    }
    header('Location: cart.php');
    exit;
}

$products = []; $total = 0;
$ids = array_keys($_SESSION['cart']);
if ($ids) {
    $place = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $mysqli->prepare("SELECT p.*, b.name AS brand FROM products p JOIN brands b ON b.id=p.brand_id WHERE p.id IN ($place)");
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
        $r['quantity'] = (int)$_SESSION['cart'][$r['id']];
        $r['subtotal'] = $r['quantity'] * $r['price'];
        $total += $r['subtotal'];
        $products[] = $r;
    }
    $stmt->close();
}

head('Корзина');
?>
<h1 class="page-title">Корзина</h1>
<?php if (!$products): ?>
  <div class="empty empty--lg">Ваша корзина пуста.<br><a href="index.php" class="btn btn--primary">Перейти в каталог</a></div>
<?php else: ?>
  <div class="cart">
    <div class="cart__items">
      <?php foreach ($products as $p): ?>
        <div class="cart__row">
          <a class="cart__img" href="product.php?id=<?= (int)$p['id'] ?>"><img src="image.php?id=<?= (int)$p['id'] ?>" alt=""></a>
          <div class="cart__info">
            <div class="cart__brand"><?= h($p['brand']) ?></div>
            <a class="cart__title" href="product.php?id=<?= (int)$p['id'] ?>"><?= h($p['name']) ?></a>
            <div class="cart__model"><?= h($p['model']) ?></div>
          </div>
          <form class="cart__qty" method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="number" name="quantity" value="<?= (int)$p['quantity'] ?>" min="1">
            <button type="submit">Обновить</button>
          </form>
          <div class="cart__sum"><?= money($p['subtotal']) ?></div>
          <form class="cart__del" method="post">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <button type="submit" title="Удалить">✕</button>
          </form>
        </div>
      <?php endforeach; ?>
      <form method="post" class="cart__clear">
        <input type="hidden" name="action" value="clear">
        <button class="btn btn--ghost">Очистить корзину</button>
      </form>
    </div>
    <aside class="cart__total">
      <div class="cart__total-row"><span>Товаров:</span><b><?= count($products) ?></b></div>
      <div class="cart__total-row"><span>Итого:</span><b><?= money($total) ?></b></div>
      <a href="checkout.php" class="btn btn--primary btn--big btn--full">Оформить заказ →</a>
      <div class="cart__hint">Бесплатная доставка при заказе от 5 000 ₽</div>
    </aside>
  </div>
<?php endif; ?>
<?php foot(); ?>

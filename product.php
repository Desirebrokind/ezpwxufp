<?php
require_once __DIR__ . '/db.php';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!uid()) { flash('error', 'Войдите, чтобы оставить отзыв'); header('Location: login.php'); exit; }
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $text = trim($_POST['text'] ?? '');
    if (strlen($text) < 10) { flash('error', 'Отзыв должен быть не короче 10 символов'); }
    else {
        $stmt = $mysqli->prepare("INSERT INTO reviews (user_id, product_id, rating, text) VALUES (?, ?, ?, ?)");
        $uid = uid();
        $stmt->bind_param('iiis', $uid, $id, $rating, $text);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Спасибо за отзыв!');
    }
    header('Location: product.php?id=' . $id);
    exit;
}

$p = fetch_one($mysqli,
    "SELECT p.*, c.name AS category, b.name AS brand
     FROM products p JOIN categories c ON c.id=p.category_id JOIN brands b ON b.id=p.brand_id
     WHERE p.id=?", 'i', [$id]);

if (!$p) {
    http_response_code(404);
    head('Товар не найден');
    echo '<div class="empty">Товар не найден. <a href="index.php">Перейти в каталог</a></div>';
    foot();
    exit;
}

$reviews = fetch_all($mysqli,
    "SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC",
    'i', [$id]);

$avg = 0;
if ($reviews) {
    $sum = 0;
    foreach ($reviews as $r) $sum += (int)$r['rating'];
    $avg = $sum / count($reviews);
}

head($p['name']);
?>
<nav class="crumbs">
  <a href="index.php">Каталог</a> /
  <a href="index.php?category=<?= (int)$p['category_id'] ?>"><?= h($p['category']) ?></a> /
  <span><?= h($p['name']) ?></span>
</nav>

<section class="product">
  <div class="product__gallery"><img src="image.php?id=<?= (int)$p['id'] ?>" alt="<?= h($p['name']) ?>"></div>
  <div class="product__info">
    <div class="product__brand"><?= h($p['brand']) ?></div>
    <h1 class="product__title"><?= h($p['name']) ?></h1>
    <div class="product__model">Модель: <?= h($p['model']) ?></div>
    <?php if ($reviews): ?>
      <div class="product__rating"><span class="stars" data-r="<?= round($avg) ?>">★★★★★</span><span><?= number_format($avg, 1, '.', '') ?> · <?= count($reviews) ?> отзывов</span></div>
    <?php endif; ?>
    <div class="product__price"><?= money($p['price']) ?></div>
    <div class="product__stock"><?= $p['stock'] > 0 ? 'В наличии: ' . (int)$p['stock'] . ' шт.' : 'Нет в наличии' ?></div>
    <form action="cart.php" method="post" class="product__buy">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
      <input type="number" name="quantity" value="1" min="1" max="<?= max(1, (int)$p['stock']) ?>">
      <button class="btn btn--primary btn--big" type="submit"<?= $p['stock'] <= 0 ? ' disabled' : '' ?>>В корзину</button>
    </form>
    <ul class="product__specs">
      <li><span>Категория</span><b><?= h($p['category']) ?></b></li>
      <li><span>Бренд</span><b><?= h($p['brand']) ?></b></li>
      <li><span>Модель</span><b><?= h($p['model']) ?></b></li>
      <?php if ($p['memory'] && $p['memory'] !== '—'): ?><li><span>Память</span><b><?= h($p['memory']) ?></b></li><?php endif; ?>
      <?php if ($p['screen'] && $p['screen'] !== '—'): ?><li><span>Экран</span><b><?= h($p['screen']) ?></b></li><?php endif; ?>
      <?php if ($p['color']): ?><li><span>Цвет</span><b><?= h($p['color']) ?></b></li><?php endif; ?>
    </ul>
  </div>
</section>

<section class="block"><h2>Описание</h2><p class="product__desc"><?= nl2br(h($p['description'])) ?></p></section>

<section class="block">
  <h2>Отзывы покупателей</h2>
  <?php if (!$reviews): ?>
    <div class="empty">Пока нет отзывов. Будьте первым!</div>
  <?php else: ?>
    <div class="reviews">
      <?php foreach ($reviews as $r): ?>
        <article class="review">
          <header>
            <b><?= h($r['full_name']) ?></b>
            <span class="stars" data-r="<?= (int)$r['rating'] ?>">★★★★★</span>
            <time><?= h(date('d.m.Y', strtotime($r['created_at']))) ?></time>
          </header>
          <p><?= h($r['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if (uid()): ?>
    <form method="post" class="review-form">
      <input type="hidden" name="review_submit" value="1">
      <label>Оценка<select name="rating">
        <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= $i ?> ★</option><?php endfor; ?>
      </select></label>
      <textarea name="text" placeholder="Поделитесь впечатлениями…" required minlength="10"></textarea>
      <button type="submit" class="btn btn--primary">Оставить отзыв</button>
    </form>
  <?php else: ?>
    <p>Чтобы оставить отзыв, <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a>.</p>
  <?php endif; ?>
</section>
<?php foot(); ?>

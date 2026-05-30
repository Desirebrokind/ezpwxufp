<?php
require_once __DIR__ . '/db.php';

$cat = (int)($_GET['category'] ?? 0);
$brand = (int)($_GET['brand'] ?? 0);
$pmin = $_GET['price_min'] ?? '';
$pmax = $_GET['price_max'] ?? '';
$mem = trim($_GET['memory'] ?? '');
$q = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'new';

$where = ['1=1']; $types = ''; $params = [];
if ($cat) { $where[] = 'p.category_id=?'; $types .= 'i'; $params[] = $cat; }
if ($brand) { $where[] = 'p.brand_id=?'; $types .= 'i'; $params[] = $brand; }
if ($pmin !== '') { $where[] = 'p.price>=?'; $types .= 'd'; $params[] = (float)$pmin; }
if ($pmax !== '') { $where[] = 'p.price<=?'; $types .= 'd'; $params[] = (float)$pmax; }
if ($mem !== '') { $where[] = 'p.memory=?'; $types .= 's'; $params[] = $mem; }
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.model LIKE ? OR p.description LIKE ?)';
    $l = '%' . $q . '%'; $types .= 'sss';
    array_push($params, $l, $l, $l);
}

$order = ['new' => 'p.created_at DESC', 'price_asc' => 'p.price ASC', 'price_desc' => 'p.price DESC', 'name' => 'p.name ASC'][$sort] ?? 'p.created_at DESC';

$products = fetch_all($mysqli,
    "SELECT p.*, c.name AS category, b.name AS brand
     FROM products p JOIN categories c ON c.id=p.category_id JOIN brands b ON b.id=p.brand_id
     WHERE " . implode(' AND ', $where) . " ORDER BY $order", $types, $params);

$brands = $mysqli->query("SELECT id, name FROM brands ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$mems = $mysqli->query("SELECT DISTINCT memory FROM products WHERE memory<>'—' AND memory IS NOT NULL ORDER BY memory")->fetch_all(MYSQLI_ASSOC);

head('Каталог');
?>
<?php if (!$q && !$cat && !$brand && $pmin === '' && $pmax === '' && $mem === ''): ?>
<section class="hero">
  <div class="hero__text">
    <span class="hero__badge">Новый сезон 2026</span>
    <h1>Цифровая техника<br>для жизни и работы</h1>
    <p>Ноутбуки, смартфоны, аудио, ТВ и аксессуары от ведущих мировых брендов с официальной гарантией и быстрой доставкой по России.</p>
    <div class="hero__cta">
      <a href="#catalog" class="btn btn--primary">К каталогу →</a>
      <a href="index.php?category=2" class="btn btn--ghost">Смартфоны</a>
    </div>
  </div>
  <div class="hero__cards">
    <div class="hero__card hc-1"><div class="hc__t">Apple</div><div class="hc__b">До 30 000 ₽ выгоды</div></div>
    <div class="hero__card hc-2"><div class="hc__t">Samsung</div><div class="hc__b">Кэшбек 10%</div></div>
    <div class="hero__card hc-3"><div class="hc__t">Доставка</div><div class="hc__b">Бесплатно от 5 000 ₽</div></div>
    <div class="hero__card hc-4"><div class="hc__t">Рассрочка</div><div class="hc__b">0-0-12 месяцев</div></div>
  </div>
</section>
<?php endif; ?>

<section class="layout" id="catalog">
<aside class="filters">
  <form method="get">
    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
    <div class="filters__title">Фильтры</div>
    <label>Категория<select name="category"><option value="0">Все</option>
      <?php foreach (cats($mysqli) as $c): ?><option value="<?= (int)$c['id'] ?>"<?= $cat === (int)$c['id'] ? ' selected' : '' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Бренд<select name="brand"><option value="0">Все</option>
      <?php foreach ($brands as $b): ?><option value="<?= (int)$b['id'] ?>"<?= $brand === (int)$b['id'] ? ' selected' : '' ?>><?= h($b['name']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Цена, ₽<div class="filters__price">
      <input type="number" name="price_min" placeholder="от" value="<?= h($pmin) ?>">
      <input type="number" name="price_max" placeholder="до" value="<?= h($pmax) ?>">
    </div></label>
    <label>Память<select name="memory"><option value="">Любая</option>
      <?php foreach ($mems as $m): ?><option value="<?= h($m['memory']) ?>"<?= $mem === $m['memory'] ? ' selected' : '' ?>><?= h($m['memory']) ?></option><?php endforeach; ?>
    </select></label>
    <label>Сортировка<select name="sort">
      <option value="new"<?= $sort === 'new' ? ' selected' : '' ?>>Сначала новые</option>
      <option value="price_asc"<?= $sort === 'price_asc' ? ' selected' : '' ?>>Цена ↑</option>
      <option value="price_desc"<?= $sort === 'price_desc' ? ' selected' : '' ?>>Цена ↓</option>
      <option value="name"<?= $sort === 'name' ? ' selected' : '' ?>>По названию</option>
    </select></label>
    <button class="btn btn--primary btn--full" type="submit">Применить</button>
    <a href="index.php" class="btn btn--ghost btn--full">Сбросить</a>
  </form>
</aside>

<section class="catalog">
  <div class="catalog__head">
    <h2><?php
      if ($cat) { foreach (cats($mysqli) as $c) if ((int)$c['id'] === $cat) echo h($c['name']); }
      elseif ($q !== '') echo 'Поиск: «' . h($q) . '»';
      else echo 'Все товары';
    ?></h2>
    <div class="catalog__count">Найдено: <b><?= count($products) ?></b></div>
  </div>
  <?php if (!$products): ?>
    <div class="empty">По заданным условиям ничего не найдено.</div>
  <?php else: ?>
    <div class="cards">
      <?php foreach ($products as $p): ?>
        <a class="card" href="product.php?id=<?= (int)$p['id'] ?>">
          <div class="card__img"><img src="image.php?id=<?= (int)$p['id'] ?>" alt="<?= h($p['name']) ?>" loading="lazy"></div>
          <div class="card__body">
            <div class="card__brand"><?= h($p['brand']) ?> · <?= h($p['category']) ?></div>
            <div class="card__title"><?= h($p['name']) ?></div>
            <div class="card__model"><?= h($p['model']) ?></div>
            <div class="card__bottom">
              <div class="card__price"><?= money($p['price']) ?></div>
              <span class="card__btn">Подробнее →</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
</section>
<?php foot(); ?>

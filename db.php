<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);

$mysqli = @new mysqli(getenv('DB_HOST') ?: '127.0.0.1', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', getenv('DB_NAME') ?: 'digital_store');
if ($mysqli->connect_errno) die('Ошибка БД: ' . htmlspecialchars($mysqli->connect_error));
$mysqli->set_charset('utf8mb4');

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($v) { return number_format((float)$v, 0, '.', ' ') . ' ₽'; }
function uid() { return (int)($_SESSION['user_id'] ?? 0); }
function aid() { return (int)($_SESSION['admin_id'] ?? 0); }

function require_user() { if (!uid()) { header('Location: login.php'); exit; } }
function require_admin() { if (!aid()) { header('Location: login.php'); exit; } }

function try_exec($stmt, $okMsg, $errMsg = 'Не удалось выполнить операцию') {
    $ok = @$stmt->execute();
    if ($ok) { flash('success', $okMsg); return true; }
    $code = $stmt->errno;
    if ($code == 1062) flash('error', 'Уже существует запись с таким значением');
    elseif ($code == 1451) flash('error', 'Нельзя удалить: на эту запись ссылаются другие данные');
    elseif ($code == 1452) flash('error', 'Ссылка на несуществующую запись');
    else flash('error', $errMsg);
    return false;
}

function cart_count() {
    $s = 0;
    foreach (($_SESSION['cart'] ?? []) as $q) $s += (int)$q;
    return $s;
}

function fetch_one($db, $sql, $types = '', $params = []) {
    $stmt = $db->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $r;
}

function fetch_all($db, $sql, $types = '', $params = []) {
    $stmt = $db->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $r;
}

function flash($key, $value = null) {
    if ($value === null) { $v = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $v; }
    $_SESSION['flash'][$key] = $value;
}

function cats($db) {
    static $c = null;
    if ($c === null) $c = $db->query("SELECT id, name, slug, icon FROM categories ORDER BY sort_order, name")->fetch_all(MYSQLI_ASSOC);
    return $c;
}

function head($title = 'Digital Store') {
    global $mysqli;
    $title = h($title) . ' — Digital Store';
    $cats = cats($mysqli);
    $q = h($_GET['q'] ?? '');
    $cur_cat = (int)($_GET['category'] ?? 0);
    $user = h($_SESSION['user_name'] ?? '');
    $cc = cart_count();
    echo <<<HTML
<!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>$title</title><link rel="stylesheet" href="style.css">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect rx='16' width='64' height='64' fill='%23111827'/%3E%3Ctext x='50%25' y='62%25' text-anchor='middle' font-family='sans-serif' font-weight='800' font-size='30' fill='%23fff'%3EDS%3C/text%3E%3C/svg%3E">
</head><body>
<header class="hdr">
  <div class="hdr__top">
    <a class="brand" href="index.php"><span class="brand__mark">DS</span><span class="brand__name">Digital Store</span></a>
    <button class="mega-btn" type="button" aria-haspopup="true" aria-expanded="false" onclick="document.body.classList.toggle('mega-open')">
      <span class="mega-btn__bars">☰</span> Каталог
    </button>
    <form class="search" action="index.php" method="get">
      <input type="text" name="q" placeholder="Найдите MacBook, iPhone, наушники…" value="$q">
      <button type="submit" aria-label="Найти">🔍</button>
    </form>
    <a href="cart.php" class="hdr__btn hdr__btn--cart" title="Корзина">
      <span class="hdr__icon">🛒</span><span class="hdr__label">Корзина</span><span class="hdr__cnt">$cc</span>
    </a>
HTML;
    if (aid()) {
        echo '<a href="admin/index.php" class="hdr__btn hdr__btn--admin" title="Админ-панель"><span class="hdr__icon">⚙</span><span class="hdr__label">Админ-панель</span></a>';
    }
    if (uid()) {
        echo '<a href="account.php" class="hdr__btn" title="Кабинет"><span class="hdr__icon">👤</span><span class="hdr__label">' . $user . '</span></a>';
        echo '<a href="logout.php" class="hdr__btn hdr__btn--ghost" title="Выйти">⤴</a>';
    } else {
        echo '<a href="login.php" class="hdr__btn"><span class="hdr__icon">👤</span><span class="hdr__label">Войти</span></a>';
    }
    echo '</div><div class="mega" onclick="document.body.classList.remove(\'mega-open\')"><div class="mega__inner" onclick="event.stopPropagation()"><div class="mega__head"><b>Все категории</b><button type="button" class="mega__close" onclick="document.body.classList.remove(\'mega-open\')">✕</button></div><div class="mega__grid">';
    foreach ($cats as $c) {
        $act = $cur_cat === (int)$c['id'] ? ' is-active' : '';
        echo '<a class="mega__tile' . $act . '" href="index.php?category=' . (int)$c['id'] . '"><span class="mega__icon">' . h($c['icon'] ?: '🔹') . '</span><span class="mega__name">' . h($c['name']) . '</span></a>';
    }
    echo '</div></div></div></header><main class="container">';
    if ($f = flash('success')) echo '<div class="flash flash--ok">' . h($f) . '</div>';
    if ($f = flash('error')) echo '<div class="flash flash--err">' . h($f) . '</div>';
}

function foot() {
    $y = date('Y');
    echo <<<HTML
</main>
<footer class="ftr"><div class="container ftr__inner">
<div><div class="ftr__title">Digital Store</div><p class="ftr__desc">Интернет-магазин цифровой техники: ноутбуки, смартфоны, аудио, телевизоры и аксессуары с гарантией и доставкой по всей России.</p></div>
<div><div class="ftr__title">Покупателям</div><a href="index.php">Каталог</a><a href="cart.php">Корзина</a><a href="account.php">Личный кабинет</a></div>
<div><div class="ftr__title">Контакты</div><div>+7 (800) 555-35-35</div><div>info@digital-store.ru</div><div>г. Москва, ул. Цифровая, 1</div></div>
</div><div class="ftr__copy">© $y Digital Store. Все права защищены.</div></footer>
</body></html>
HTML;
}

function admin_head($title = 'Админ-панель') {
    $title = h($title);
    $user = h($_SESSION['admin_name'] ?? '');
    $cur = basename($_SERVER['PHP_SELF']);
    $nav = [
        ['index.php', '📊', 'Дашборд'],
        ['products.php', '📦', 'Товары', ['product_edit.php']],
        ['categories.php', '📁', 'Категории'],
        ['brands.php', '🏷', 'Бренды'],
        ['users.php', '👤', 'Пользователи'],
        ['orders.php', '🧾', 'Заказы', ['order_view.php']],
        ['reports.php', '📈', 'Отчёты'],
        ['reviews.php', '⭐', 'Отзывы'],
        ['admins.php', '🔐', 'Администраторы'],
    ];
    echo <<<HTML
<!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>$title — Digital Store Admin</title><link rel="stylesheet" href="admin.css">
</head><body class="admin"><aside class="sb">
<div class="sb__brand"><span class="sb__logo">DS</span><div><b>Админка</b><small>$user</small></div></div>
<nav class="sb__nav">
HTML;
    foreach ($nav as $n) {
        $active = $cur === $n[0] || (isset($n[3]) && in_array($cur, $n[3], true));
        $cls = $active ? ' class="is-active"' : '';
        echo "<a href=\"{$n[0]}\"$cls><span>{$n[1]}</span> {$n[2]}</a>";
    }
    echo '</nav><div class="sb__bot"><a href="../index.php" target="_blank">↗ Открыть сайт</a><a href="logout.php">⤴ Выйти</a></div></aside><main class="adm">';
    if ($f = flash('success')) echo '<div class="alert alert--ok">' . h($f) . '</div>';
    if ($f = flash('error')) echo '<div class="alert alert--err">' . h($f) . '</div>';
}

function admin_foot() {
    echo '</main></body></html>';
}

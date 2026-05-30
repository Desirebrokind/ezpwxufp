<?php
require_once __DIR__ . '/../db.php';
if (aid()) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = fetch_one($mysqli, 'SELECT * FROM admins WHERE login=?', 's', [trim($_POST['login'] ?? '')]);
    if ($a && $a['password'] === ($_POST['password'] ?? '')) {
        $_SESSION['admin_id'] = (int)$a['id'];
        $_SESSION['admin_name'] = $a['full_name'];
        header('Location: index.php');
        exit;
    }
    $err = 'Неверный логин или пароль администратора';
}
?><!doctype html>
<html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Вход в админ-панель — Digital Store</title>
<link rel="stylesheet" href="admin.css">
</head><body class="adm-login">
<div class="adm-login__box">
  <div class="adm-login__logo">DS · Admin</div>
  <h1>Вход для администратора</h1>
  <?php if ($err): ?><div class="alert alert--err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <label>Логин<input type="text" name="login" required autocomplete="username"></label>
    <label>Пароль<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn--primary btn--full">Войти</button>
  </form>
  <p class="adm-login__hint">Тестовый аккаунт: <b>admin / admin123</b></p>
  <p><a href="../index.php">← На сайт</a></p>
</div></body></html>

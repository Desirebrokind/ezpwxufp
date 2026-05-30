<?php
require_once __DIR__ . '/db.php';
if (uid()) { header('Location: account.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pwd = $_POST['password'] ?? '';
    $a = fetch_one($mysqli, 'SELECT * FROM admins WHERE login=?', 's', [$login]);
    if ($a && $a['password'] === $pwd) {
        $_SESSION['admin_id'] = (int)$a['id'];
        $_SESSION['admin_name'] = $a['full_name'];
        flash('success', 'Здравствуйте, ' . $a['full_name'] . '. Вы вошли как администратор');
        header('Location: admin/index.php');
        exit;
    }
    $u = fetch_one($mysqli, 'SELECT * FROM users WHERE login=?', 's', [$login]);
    if ($u && $u['password'] === $pwd) {
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['user_name'] = $u['full_name'];
        flash('success', 'Добро пожаловать, ' . $u['full_name']);
        header('Location: account.php');
        exit;
    }
    flash('error', 'Неверный логин или пароль');
}

head('Вход');
?>
<div class="auth">
  <h1>Вход в личный кабинет</h1>
  <form method="post" class="auth__form">
    <label>Логин<input type="text" name="login" required autocomplete="username"></label>
    <label>Пароль<input type="password" name="password" required autocomplete="current-password"></label>
    <button type="submit" class="btn btn--primary btn--full">Войти</button>
  </form>
  <p>Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
  <div class="auth__hint">
    <div>Аккаунт покупателя: <b>ivanov / ivan123</b></div>
    <div>Аккаунт администратора: <b>admin / admin123</b></div>
  </div>
</div>
<?php foot(); ?>

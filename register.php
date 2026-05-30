<?php
require_once __DIR__ . '/db.php';
if (uid()) { header('Location: account.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $fn = trim($_POST['full_name'] ?? '');
    $em = trim($_POST['email'] ?? '');
    $ph = trim($_POST['phone'] ?? '');
    $ad = trim($_POST['address'] ?? '');
    if (strlen($login) < 3 || strlen($password) < 4 || $fn === '' || $em === '' || $ph === '' || $ad === '') {
        flash('error', 'Заполните все поля корректно');
    } elseif (fetch_one($mysqli, 'SELECT id FROM users WHERE login=?', 's', [$login])) {
        flash('error', 'Такой логин уже занят');
    } else {
        $stmt = $mysqli->prepare("INSERT INTO users (login, password, full_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $login, $password, $fn, $em, $ph, $ad);
        $stmt->execute();
        $_SESSION['user_id'] = (int)$stmt->insert_id;
        $_SESSION['user_name'] = $fn;
        $stmt->close();
        flash('success', 'Регистрация завершена!');
        header('Location: account.php');
        exit;
    }
}

head('Регистрация');
?>
<div class="auth">
  <h1>Регистрация</h1>
  <form method="post" class="auth__form">
    <label>Логин<input type="text" name="login" required minlength="3" value="<?= h($_POST['login'] ?? '') ?>"></label>
    <label>Пароль<input type="password" name="password" required minlength="4"></label>
    <label>ФИО<input type="text" name="full_name" required value="<?= h($_POST['full_name'] ?? '') ?>"></label>
    <label>E-mail<input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"></label>
    <label>Телефон<input type="tel" name="phone" required value="<?= h($_POST['phone'] ?? '') ?>"></label>
    <label>Адрес доставки<input type="text" name="address" required value="<?= h($_POST['address'] ?? '') ?>"></label>
    <button type="submit" class="btn btn--primary btn--full">Создать аккаунт</button>
  </form>
  <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
<?php foot(); ?>

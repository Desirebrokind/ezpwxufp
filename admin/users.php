<?php
require_once __DIR__ . '/../db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $login = trim($_POST['login']);
    $pwd = $_POST['password'];
    $fn = trim($_POST['full_name']);
    $em = trim($_POST['email']);
    $ph = trim($_POST['phone']);
    $ad = trim($_POST['address']);
    if ($id > 0) {
        if ($pwd === '') {
            $stmt = $mysqli->prepare("UPDATE users SET login=?, full_name=?, email=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param('sssssi', $login, $fn, $em, $ph, $ad, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET login=?, password=?, full_name=?, email=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param('ssssssi', $login, $pwd, $fn, $em, $ph, $ad, $id);
        }
        try_exec($stmt, 'Пользователь обновлён', 'Не удалось обновить пользователя');
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO users (login, password, full_name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $login, $pwd, $fn, $em, $ph, $ad);
        try_exec($stmt, 'Пользователь создан', 'Не удалось создать пользователя');
        $stmt->close();
    }
    header('Location: users.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cnt = (int)fetch_one($mysqli, 'SELECT COUNT(*) c FROM orders WHERE user_id=?', 'i', [$id])['c'];
    if ($cnt > 0) {
        flash('error', "Нельзя удалить: у пользователя $cnt заказ(ов). Сначала удалите заказы.");
    } else {
        $mysqli->query("DELETE FROM reviews WHERE user_id=$id");
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param('i', $id);
        try_exec($stmt, 'Пользователь удалён', 'Не удалось удалить пользователя');
        $stmt->close();
    }
    header('Location: users.php');
    exit;
}

$edit = isset($_GET['edit']) ? fetch_one($mysqli, 'SELECT * FROM users WHERE id=?', 'i', [(int)$_GET['edit']]) : null;
$us = $mysqli->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS oc FROM users u ORDER BY u.id DESC")->fetch_all(MYSQLI_ASSOC);

admin_head('Пользователи');
?>
<h1>Пользователи</h1>
<details class="card" <?= $edit ? 'open' : '' ?>>
  <summary><?= $edit ? 'Редактирование #'.(int)$edit['id'] : 'Добавить пользователя' ?></summary>
  <form method="post" class="form-stack form-stack--2col">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
    <label>Логин<input type="text" name="login" required value="<?= h($edit['login'] ?? '') ?>"></label>
    <label>Пароль <?= $edit ? '(пусто = не менять)' : '' ?><input type="text" name="password" <?= $edit ? '' : 'required' ?>></label>
    <label>ФИО<input type="text" name="full_name" required value="<?= h($edit['full_name'] ?? '') ?>"></label>
    <label>Email<input type="email" name="email" required value="<?= h($edit['email'] ?? '') ?>"></label>
    <label>Телефон<input type="tel" name="phone" required value="<?= h($edit['phone'] ?? '') ?>"></label>
    <label>Адрес<input type="text" name="address" required value="<?= h($edit['address'] ?? '') ?>"></label>
    <div><button class="btn btn--primary"><?= $edit ? 'Сохранить' : 'Создать' ?></button>
      <?php if ($edit): ?><a href="users.php" class="btn btn--ghost">Отмена</a><?php endif; ?></div>
  </form>
</details>
<table class="tbl tbl--wide">
  <thead><tr><th>ID</th><th>Логин</th><th>ФИО</th><th>Email</th><th>Телефон</th><th>Заказов</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($us as $u): ?>
      <tr>
        <td><?= (int)$u['id'] ?></td>
        <td><b><?= h($u['login']) ?></b></td>
        <td><?= h($u['full_name']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= h($u['phone']) ?></td>
        <td><?= (int)$u['oc'] ?></td>
        <td class="actions">
          <a href="users.php?edit=<?= (int)$u['id'] ?>" class="btn btn--small">Изм.</a>
          <a href="users.php?delete=<?= (int)$u['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить?')">×</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot(); ?>

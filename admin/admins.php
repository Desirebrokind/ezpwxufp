<?php
require_once __DIR__ . '/../db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $login = trim($_POST['login']);
    $pwd = $_POST['password'];
    $fn = trim($_POST['full_name']);
    if ($id > 0) {
        if ($pwd === '') {
            $stmt = $mysqli->prepare("UPDATE admins SET login=?, full_name=? WHERE id=?");
            $stmt->bind_param('ssi', $login, $fn, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE admins SET login=?, password=?, full_name=? WHERE id=?");
            $stmt->bind_param('sssi', $login, $pwd, $fn, $id);
        }
        try_exec($stmt, 'Администратор обновлён', 'Не удалось обновить администратора');
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO admins (login, password, full_name) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $login, $pwd, $fn);
        try_exec($stmt, 'Администратор добавлен', 'Не удалось добавить администратора');
        $stmt->close();
    }
    header('Location: admins.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id === aid()) {
        flash('error', 'Нельзя удалить себя');
    } else {
        $stmt = $mysqli->prepare("DELETE FROM admins WHERE id=?");
        $stmt->bind_param('i', $id);
        try_exec($stmt, 'Администратор удалён', 'Не удалось удалить администратора');
        $stmt->close();
    }
    header('Location: admins.php');
    exit;
}

$edit = isset($_GET['edit']) ? fetch_one($mysqli, 'SELECT * FROM admins WHERE id=?', 'i', [(int)$_GET['edit']]) : null;
$as = $mysqli->query("SELECT * FROM admins ORDER BY id")->fetch_all(MYSQLI_ASSOC);

admin_head('Администраторы');
?>
<h1>Администраторы</h1>
<div class="grid2">
  <section class="card">
    <h2><?= $edit ? 'Редактирование' : 'Добавить администратора' ?></h2>
    <form method="post" class="form-stack">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
      <label>Логин<input type="text" name="login" required value="<?= h($edit['login'] ?? '') ?>"></label>
      <label>Пароль<input type="text" name="password" <?= $edit ? '' : 'required' ?> placeholder="<?= $edit ? 'пусто = не менять' : '' ?>"></label>
      <label>ФИО<input type="text" name="full_name" required value="<?= h($edit['full_name'] ?? '') ?>"></label>
      <button class="btn btn--primary"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
      <?php if ($edit): ?><a href="admins.php" class="btn btn--ghost">Отмена</a><?php endif; ?>
    </form>
  </section>
  <section class="card">
    <h2>Список</h2>
    <table class="tbl">
      <thead><tr><th>#</th><th>Логин</th><th>ФИО</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($as as $a): ?>
          <tr>
            <td><?= (int)$a['id'] ?></td>
            <td><b><?= h($a['login']) ?></b></td>
            <td><?= h($a['full_name']) ?></td>
            <td class="actions">
              <a href="admins.php?edit=<?= (int)$a['id'] ?>" class="btn btn--small">Изм.</a>
              <?php if ((int)$a['id'] !== aid()): ?><a href="admins.php?delete=<?= (int)$a['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить?')">×</a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
<?php admin_foot(); ?>

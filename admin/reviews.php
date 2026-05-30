<?php
require_once __DIR__ . '/../db.php';
require_admin();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM reviews WHERE id=?");
    $stmt->bind_param('i', $id);
    try_exec($stmt, 'Отзыв удалён', 'Не удалось удалить отзыв');
    $stmt->close();
    header('Location: reviews.php');
    exit;
}

$rs = $mysqli->query("SELECT r.*, u.full_name, p.name AS product FROM reviews r JOIN users u ON u.id=r.user_id JOIN products p ON p.id=r.product_id ORDER BY r.created_at DESC")->fetch_all(MYSQLI_ASSOC);

admin_head('Отзывы');
?>
<h1>Отзывы <span class="muted">(<?= count($rs) ?>)</span></h1>
<table class="tbl tbl--wide">
  <thead><tr><th>ID</th><th>Дата</th><th>Автор</th><th>Товар</th><th>Оценка</th><th>Текст</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($rs as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= h(date('d.m.Y', strtotime($r['created_at']))) ?></td>
        <td><?= h($r['full_name']) ?></td>
        <td><?= h($r['product']) ?></td>
        <td><span class="stars" data-r="<?= (int)$r['rating'] ?>">★★★★★</span></td>
        <td><?= h(mb_substr($r['text'], 0, 120)) ?><?= mb_strlen($r['text']) > 120 ? '…' : '' ?></td>
        <td class="actions"><a href="reviews.php?delete=<?= (int)$r['id'] ?>" class="btn btn--small btn--danger" onclick="return confirm('Удалить?')">×</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php admin_foot(); ?>

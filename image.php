<?php
require_once __DIR__ . '/db.php';
$row = fetch_one($mysqli, 'SELECT image, image_mime FROM products WHERE id=?', 'i', [(int)($_GET['id'] ?? 0)]);
if (!$row || !$row['image']) {
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600"><rect width="600" height="600" fill="#f1f5f9"/><text x="300" y="320" text-anchor="middle" font-size="48" font-family="sans-serif" fill="#94a3b8">DS</text></svg>';
    exit;
}
header('Content-Type: ' . $row['image_mime']);
header('Cache-Control: public, max-age=86400');
echo $row['image'];

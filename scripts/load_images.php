<?php
$mysqli = new mysqli('127.0.0.1', 'devin', 'devin', 'digital_store');
if ($mysqli->connect_errno) die($mysqli->connect_error);
$mysqli->set_charset('utf8mb4');
$dir = __DIR__ . '/images';
for ($pid = 1; $pid <= 28; $pid++) {
    $f = sprintf('%s/product_%02d.jpg', $dir, $pid);
    if (!file_exists($f)) { echo "miss $pid\n"; continue; }
    $data = file_get_contents($f);
    $null = NULL;
    $stmt = $mysqli->prepare("UPDATE products SET image=?, image_mime='image/jpeg' WHERE id=?");
    $stmt->bind_param('bi', $null, $pid);
    $stmt->send_long_data(0, $data);
    if ($stmt->execute()) {
        echo "#$pid ok " . strlen($data) . " bytes\n";
    } else {
        echo "#$pid err: " . $stmt->error . "\n";
    }
    $stmt->close();
}

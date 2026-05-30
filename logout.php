<?php
require_once __DIR__ . '/db.php';
unset($_SESSION['user_id'], $_SESSION['user_name']);
flash('success', 'Вы вышли из аккаунта');
header('Location: index.php');

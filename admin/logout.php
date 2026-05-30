<?php
require_once __DIR__ . '/../db.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
header('Location: login.php');

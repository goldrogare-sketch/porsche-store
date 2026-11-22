<?php
require_once 'things/db.php';

$isAdmin = false;

if (isset($_SESSION['user_id'])) {
    $query = $database->prepare('SELECT role FROM users WHERE id = ?');
    $query->execute([$_SESSION['user_id']]);
    $role = $query->fetchColumn();

    if ($role == 'admin') {
        $isAdmin = true;
    }
}

if ($isAdmin == false) {
    header('Location: index.php');
    exit;
}
<?php
require_once 'config/db.php';

$destinasi = $pdo->query(
    "SELECT 
        *
    FROM destinasi
    ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);
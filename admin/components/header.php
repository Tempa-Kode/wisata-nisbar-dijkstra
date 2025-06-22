<?php
session_start();
require_once '../config/db.php';

// cek apakah admin sudah login
if (!isset($_SESSION['id'])) {
    header('Location: /admin/login.php');
    exit;
}
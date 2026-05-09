<?php
require_once 'config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM moto WHERE id_moto=?")->execute([$id]);
header("Location: moto_lista.php");
<?php
require_once 'config.php';
checkLogin();
$pdo->prepare("DELETE FROM pezzo_ricambio WHERE id_pezzo=?")->execute([$_GET['id']]);
header("Location: pezzi_lista.php");
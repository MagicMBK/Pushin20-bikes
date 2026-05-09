<?php
require_once 'config.php';
checkLogin();
$pdo->prepare("DELETE FROM intervento WHERE id_intervento=?")->execute([$_GET['id']]);
header("Location: interventi_lista.php");
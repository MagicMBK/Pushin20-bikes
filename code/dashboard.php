<?php
require_once 'config.php';
checkLogin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Pushin20 Bikes</title>
</head>
<body>
    <h1>Dashboard Officina Moto</h1>
    
    <p>Benvenuto, <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['ruolo']) ?>)</p>
    
    <h2>Menu</h2>
    <ul>
        <li><a href="clienti_lista.php">Gestione Clienti</a></li>
        <li><a href="moto_lista.php">Gestione Moto</a></li>
        <li><a href="interventi_lista.php">Gestione Interventi</a></li>
        <li><a href="pezzi_lista.php">Gestione Pezzi Ricambio</a></li>
        <li><a href="fornitori_lista.php">Gestione Fornitori</a></li>
        <li><a href="meccanici_lista.php">Gestione Meccanici</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</body>
</html>
<?php
require_once 'config.php';
checkLogin();

$ruolo = $_SESSION['ruolo'];

// Per il cliente mostriamo le sue moto e i suoi interventi
$mie_moto = [];
$miei_interventi = [];

if ($ruolo === 'cliente') {
    $stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_utente = ?");
    $stmt->execute([$_SESSION['id_utente']]);
    $cli = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cli) {
        $id_cliente = $cli['id_cliente'];

        $stmt = $pdo->prepare("SELECT * FROM moto WHERE id_cliente = ? ORDER BY marca, modello");
        $stmt->execute([$id_cliente]);
        $mie_moto = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT i.*, m.marca, m.modello, m.targa,
                   mec.nome AS mec_nome, mec.cognome AS mec_cognome
            FROM intervento i
            JOIN moto m ON i.id_moto = m.id_moto
            JOIN meccanico mec ON i.id_meccanico = mec.id_meccanico
            WHERE m.id_cliente = ?
            ORDER BY i.data_ingresso DESC
        ");
        $stmt->execute([$id_cliente]);
        $miei_interventi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Dashboard - Pushin20 Bikes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
    Pushin20 Bikes |
    <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($ruolo) ?>)
    | <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h1>Dashboard</h1>

<?php if ($ruolo === 'admin'): ?>

<h2>Pannello Admin</h2>
<ul>
    <li><a href="utenti.php">Gestione Utenti</a></li>
    <li><a href="clienti.php">Gestione Clienti</a></li>
    <li><a href="meccanici.php">Gestione Meccanici</a></li>
    <li><a href="moto_lista.php">Gestione Moto</a></li>
    <li><a href="interventi_lista.php">Gestione Interventi</a></li>
    <li><a href="pezzi_lista.php">Gestione Pezzi Ricambio</a></li>
    <li><a href="fornitori_lista.php">Gestione Fornitori</a></li>
</ul>

<?php elseif ($ruolo === 'meccanico'): ?>

<h2>Pannello Meccanico</h2>
<ul>
    <li><a href="interventi_lista.php">I miei interventi</a></li>
    <li><a href="moto_lista.php">Moto in officina</a></li>
    <li><a href="pezzi_lista.php">Pezzi di ricambio</a></li>
    <li><a href="clienti.php">Clienti</a></li>
</ul>

<?php else: ?>

<h2>Menu Cliente</h2>
<ul>
    <li><a href="mie_moto.php">Le mie Moto</a></li>
    <li><a href="miei_interventi.php">I miei Interventi</a></li>
</ul>

<?php endif; ?>

</div>
</body>
</html>
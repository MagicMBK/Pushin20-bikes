<?php
require_once 'config.php';
checkRole(['admin', 'meccanico']);

$where = "";
if ($_SESSION['ruolo'] === 'meccanico') {
    $r = $pdo->prepare("SELECT id_meccanico FROM meccanico WHERE id_utente=?");
    $r->execute([$_SESSION['id_utente']]);
    $id_mec = $r->fetchColumn();
    $where = "WHERE i.id_meccanico = $id_mec AND i.stato != 'completato'";
}

$lista = $pdo->query("
    SELECT DISTINCT m.targa, m.marca, m.modello, m.anno,
           c.nome, c.cognome, i.stato
    FROM moto m
    JOIN cliente c ON m.id_cliente = c.id_cliente
    LEFT JOIN intervento i ON i.id_moto = m.id_moto
    $where
    ORDER BY m.marca, m.modello
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Moto in officina</title></head>
<body>
<a href="dashboard.php">← Dashboard</a>
<h1>Moto in officina</h1>
<table border="1" cellpadding="6">
    <tr>
        <th>Targa</th><th>Moto</th><th>Anno</th><th>Proprietario</th><th>Stato</th>
    </tr>
    <?php foreach ($lista as $m): ?>
    <tr>
        <td><?= htmlspecialchars($m['targa']) ?></td>
        <td><?= htmlspecialchars($m['marca'].' '.$m['modello']) ?></td>
        <td><?= $m['anno'] ?></td>
        <td><?= htmlspecialchars($m['nome'].' '.$m['cognome']) ?></td>
        <td><?= $m['stato'] ?? '–' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

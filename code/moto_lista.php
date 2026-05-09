<?php
require_once 'config.php';
checkLogin();

$stmt = $pdo->query("SELECT m.*, c.nome, c.cognome 
                     FROM moto m 
                     JOIN cliente c ON m.id_cliente = c.id_cliente 
                     ORDER BY m.targa");
$moto = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lista Moto</title>
</head>
<body>
    <h1>Lista Moto</h1>
    <a href="dashboard.php">Torna alla Dashboard</a> | 
    <a href="moto_inserisci.php">Nuova Moto</a>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Targa</th>
            <th>Marca</th>
            <th>Modello</th>
            <th>Anno</th>
            <th>Proprietario</th>
            <th>Azioni</th>
        </tr>
        <?php foreach($moto as $m): ?>
        <tr>
            <td><?= $m['id_moto'] ?></td>
            <td><?= htmlspecialchars($m['targa']) ?></td>
            <td><?= htmlspecialchars($m['marca']) ?></td>
            <td><?= htmlspecialchars($m['modello']) ?></td>
            <td><?= $m['anno'] ?></td>
            <td><?= htmlspecialchars($m['nome'] . ' ' . $m['cognome']) ?></td>
            <td>
                <a href="moto_modifica.php?id=<?= $m['id_moto'] ?>">Modifica</a> |
                <a href="moto_elimina.php?id=<?= $m['id_moto'] ?>" 
                   onclick="return confirm('Sicuro di eliminare?')">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
<?php
require_once 'config.php';
checkRole(['admin', 'meccanico']);

$lista = $pdo->query("
    SELECT p.codice, p.nome, p.quantita_stock, p.scorta_minima,
           p.prezzo_vendita, f.ragione_sociale
    FROM pezzo_ricambio p
    JOIN fornitore f ON p.id_fornitore = f.id_fornitore
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Pezzi di ricambio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php">← Dashboard</a>
    <h1>Pezzi di ricambio</h1>
    <table>
        <thead>
            <tr>
                <th>Codice</th>
                <th>Nome</th>
                <th>Fornitore</th>
                <th>Stock</th>
                <th>Prezzo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista as $p): ?>
            <?php $scarso = $p['quantita_stock'] <= $p['scorta_minima']; ?>
            <tr <?= $scarso ? 'style="background:#ffe0e0"' : '' ?>>
                <td><?= htmlspecialchars($p['codice']) ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['ragione_sociale']) ?></td>
                <td><?= $p['quantita_stock'] ?><?= $scarso ? ' ⚠️' : '' ?></td>
                <td>€ <?= number_format($p['prezzo_vendita'] / 100, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php
require_once 'config.php';
checkLogin();
$pezzi = $pdo->query("SELECT * FROM pezzo_ricambio")->fetchAll();
?>
<h1>Pezzi</h1>
<a href="pezzo_inserisci.php">Nuovo</a>
<table border="1">
    <tr>
        <th>Nome</th>
        <th>Stock</th>
        <th>Azioni</th>
    </tr>
    <?php foreach ($pezzi as $p): ?>
        <tr>
            <td><?= $p['nome'] ?></td>
            <td><?= $p['quantita_stock'] ?></td>
            <td>
                <a href="pezzo_modifica.php?id=<?= $p['id_pezzo'] ?>">Modifica</a>
                <a href="pezzo_elimina.php?id=<?= $p['id_pezzo'] ?>">Elimina</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
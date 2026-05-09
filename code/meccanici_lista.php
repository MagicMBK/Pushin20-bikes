<?php
require_once 'config.php';
checkLogin();
$meccanici = $pdo->query("SELECT * FROM meccanico")->fetchAll();
?>
<h1>Meccanici</h1>
<table border="1">
    <?php foreach ($meccanici as $m): ?>
        <tr>
            <td><?= $m['nome'] . ' ' . $m['cognome'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
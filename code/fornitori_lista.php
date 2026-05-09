<?php
require_once 'config.php';
checkLogin();
$fornitori=$pdo->query("SELECT * FROM fornitore")->fetchAll();
?>
<h1>Fornitori</h1>
<a href="fornitore_inserisci.php">Nuovo</a>
<table border="1">
<?php foreach($fornitori as $f): ?>
<tr>
<td><?= $f['ragione_sociale'] ?></td>
<td>
<a href="fornitore_elimina.php?id=<?= $f['id_fornitore'] ?>">Elimina</a>
</td>
</tr>
<?php endforeach; ?>
</table>
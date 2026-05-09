<?php
require_once 'config.php';
checkLogin();

$interventi = $pdo->query("
SELECT i.*, m.targa, mec.nome, mec.cognome
FROM intervento i
JOIN moto m ON i.id_moto=m.id_moto
JOIN meccanico mec ON i.id_meccanico=mec.id_meccanico
")->fetchAll();
?>
<h1>Interventi</h1>
<a href="intervento_inserisci.php">Nuovo</a>
<table border="1">
<tr><th>ID</th><th>Moto</th><th>Meccanico</th><th>Stato</th><th>Azioni</th></tr>
<?php foreach($interventi as $i): ?>
<tr>
<td><?= $i['id_intervento'] ?></td>
<td><?= $i['targa'] ?></td>
<td><?= $i['nome'].' '.$i['cognome'] ?></td>
<td><?= $i['stato'] ?></td>
<td>
<a href="intervento_modifica.php?id=<?= $i['id_intervento'] ?>">Modifica</a>
<a href="intervento_elimina.php?id=<?= $i['id_intervento'] ?>">Elimina</a>
</td>
</tr>
<?php endforeach; ?>
</table>
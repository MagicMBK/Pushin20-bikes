<?php
require_once 'config.php';
checkRole(['admin', 'meccanico']);

// Aggiorna stato
if ($_POST['id'] ?? false) {
    $pdo->prepare("UPDATE intervento SET stato=? WHERE id_intervento=?")
        ->execute([$_POST['stato'], $_POST['id']]);
    header('Location: interventi_lista.php'); exit;
}

// Filtra per meccanico loggato
$where = "";
if ($_SESSION['ruolo'] === 'meccanico') {
    $r = $pdo->prepare("SELECT id_meccanico FROM meccanico WHERE id_utente=?");
    $r->execute([$_SESSION['id_utente']]);
    $id_mec = $r->fetchColumn();
    $where = "WHERE i.id_meccanico = $id_mec";
}

$lista = $pdo->query("
    SELECT i.id_intervento, i.data_ingresso, i.stato,
           m.targa, m.marca, m.modello,
           c.nome, c.cognome
    FROM intervento i
    JOIN moto m ON i.id_moto = m.id_moto
    JOIN cliente c ON m.id_cliente = c.id_cliente
    $where
    ORDER BY i.data_ingresso DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Interventi</title></head>
<body>
<a href="dashboard.php">← Dashboard</a>
<h1>I miei interventi</h1>
<table border="1" cellpadding="6">
    <tr>
        <th>Moto</th><th>Cliente</th><th>Ingresso</th><th>Stato</th><th>Aggiorna</th>
    </tr>
    <?php foreach ($lista as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['marca'].' '.$i['modello'].' ('.$i['targa'].')') ?></td>
        <td><?= htmlspecialchars($i['nome'].' '.$i['cognome']) ?></td>
        <td><?= $i['data_ingresso'] ?></td>
        <td><?= $i['stato'] ?></td>
        <td>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $i['id_intervento'] ?>">
                <select name="stato">
                    <?php foreach (['attesa', 'in_corso', 'completato'] as $s): ?>
                    <option <?= $i['stato'] == $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <button>Salva</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

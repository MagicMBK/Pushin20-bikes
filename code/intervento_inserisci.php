<?php
require_once 'config.php';
checkLogin();

$moto = $pdo->query("SELECT * FROM moto")->fetchAll();
$meccanici = $pdo->query("SELECT * FROM meccanico")->fetchAll();

if ($_POST) {
    $pdo->prepare("INSERT INTO intervento (data_ingresso, stato, id_moto, id_meccanico)
VALUES (?,?,?,?)")
        ->execute([$_POST['data_ingresso'], $_POST['stato'], $_POST['id_moto'], $_POST['id_meccanico']]);
    header("Location: interventi_lista.php");
}
?>
<h1>Nuovo Intervento</h1>
<form method="POST">
    Data ingresso: <input type="date" name="data_ingresso"><br>
    Stato:
    <select name="stato">
        <option>attesa</option>
        <option>in_lavorazione</option>
        <option>completato</option>
    </select><br>
    Moto:
    <select name="id_moto">
        <?php foreach ($moto as $m): ?>
            <option value="<?= $m['id_moto'] ?>"><?= $m['targa'] ?></option>
        <?php endforeach; ?>
    </select><br>
    Meccanico:
    <select name="id_meccanico">
        <?php foreach ($meccanici as $m): ?>
            <option value="<?= $m['id_meccanico'] ?>"><?= $m['nome'] ?></option>
        <?php endforeach; ?>
    </select><br>
    <button>Salva</button>
</form>
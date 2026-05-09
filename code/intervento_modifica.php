<?php
require_once 'config.php';
checkLogin();

$id = $_GET['id'];
$intervento = $pdo->prepare("SELECT * FROM intervento WHERE id_intervento=?");
$intervento->execute([$id]);
$intervento = $intervento->fetch();

if ($_POST) {
    $pdo->prepare("UPDATE intervento SET stato=? WHERE id_intervento=?")
        ->execute([$_POST['stato'], $id]);
    header("Location: interventi_lista.php");
}
?>
<h1>Modifica Intervento</h1>
<form method="POST">
    Stato:
    <select name="stato">
        <option <?= $intervento['stato'] == 'attesa' ? 'selected' : '' ?>>attesa</option>
        <option <?= $intervento['stato'] == 'in_lavorazione' ? 'selected' : '' ?>>in_lavorazione</option>
        <option <?= $intervento['stato'] == 'completato' ? 'selected' : '' ?>>completato</option>
    </select>
    <button>Salva</button>
</form>
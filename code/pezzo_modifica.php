<?php
require_once 'config.php';
checkLogin();
$id = $_GET['id'];
if ($_POST) {
    $pdo->prepare("UPDATE pezzo_ricambio SET nome=? WHERE id_pezzo=?")
        ->execute([$_POST['nome'], $id]);
    header("Location: pezzi_lista.php");
}
?>
<h1>Modifica Pezzo</h1>
<form method="POST">
    Nome: <input name="nome">
    <button>Salva</button>
</form>
<?php
require_once 'config.php';
checkLogin();

if ($_POST) {
    $pdo->prepare("INSERT INTO pezzo_ricambio (codice,nome,prezzo_vendita,id_fornitore)
VALUES (?,?,?,?)")
        ->execute([$_POST['codice'], $_POST['nome'], $_POST['prezzo'], $_POST['id_fornitore']]);
    header("Location: pezzi_lista.php");
}
?>
<h1>Nuovo Pezzo</h1>
<form method="POST">
    Codice: <input name="codice"><br>
    Nome: <input name="nome"><br>
    Prezzo: <input name="prezzo"><br>
    Fornitore ID: <input name="id_fornitore"><br>
    <button>Salva</button>
</form>
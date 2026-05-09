<?php
require_once 'config.php';
checkLogin();

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM moto WHERE id_moto=?");
$stmt->execute([$id]);
$moto = $stmt->fetch();

if (!$moto)
    die("Moto non trovata");

$clienti = $pdo->query("SELECT * FROM cliente ORDER BY cognome")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE moto SET targa=?, marca=?, modello=?, anno=?, id_cliente=? WHERE id_moto=?");
    $stmt->execute([
        $_POST['targa'],
        $_POST['marca'],
        $_POST['modello'],
        $_POST['anno'],
        $_POST['id_cliente'],
        $id
    ]);
    header("Location: moto_lista.php");
}
?>
<h1>Modifica Moto</h1>
<form method="POST">
    Targa: <input type="text" name="targa" value="<?= $moto['targa'] ?>"><br>
    Marca: <input type="text" name="marca" value="<?= $moto['marca'] ?>"><br>
    Modello: <input type="text" name="modello" value="<?= $moto['modello'] ?>"><br>
    Anno: <input type="number" name="anno" value="<?= $moto['anno'] ?>"><br>
    Cliente:
    <select name="id_cliente">
        <?php foreach ($clienti as $c): ?>
            <option value="<?= $c['id_cliente'] ?>" <?= $c['id_cliente'] == $moto['id_cliente'] ? 'selected' : '' ?>>
                <?= $c['cognome'] . ' ' . $c['nome'] ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <button>Salva</button>
</form>
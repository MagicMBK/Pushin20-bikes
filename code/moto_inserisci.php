<?php
require_once 'config.php';
checkLogin();

$errore = '';
$successo = '';

$clienti = $pdo->query("SELECT id_cliente, nome, cognome FROM cliente ORDER BY cognome, nome")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $targa = $_POST['targa'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modello = $_POST['modello'] ?? '';
    $anno = $_POST['anno'] ?? '';
    $id_cliente = $_POST['id_cliente'] ?? '';
    
    if (empty($targa) || empty($marca) || empty($modello) || empty($anno) || empty($id_cliente)) {
        $errore = 'Compila tutti i campi';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO moto (targa, marca, modello, anno, id_cliente) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$targa, $marca, $modello, $anno, $id_cliente]);
            $successo = 'Moto inserita con successo';
        } catch(PDOException $e) {
            $errore = 'Errore inserimento: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inserisci Moto</title>
</head>
<body>
    <h1>Inserisci Nuova Moto</h1>
    <a href="moto_lista.php">Torna alla lista</a>
    
    <?php if ($errore): ?>
        <p style="color: red;"><?= htmlspecialchars($errore) ?></p>
    <?php endif; ?>
    
    <?php if ($successo): ?>
        <p style="color: green;"><?= htmlspecialchars($successo) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Targa*:</label><br>
            <input type="text" name="targa" required>
        </div>
        <div>
            <label>Marca*:</label><br>
            <input type="text" name="marca" required>
        </div>
        <div>
            <label>Modello*:</label><br>
            <input type="text" name="modello" required>
        </div>
        <div>
            <label>Anno*:</label><br>
            <input type="number" name="anno" min="1900" max="2099" required>
        </div>
        <div>
            <label>Cliente*:</label><br>
            <select name="id_cliente" required>
                <option value="">Seleziona cliente</option>
                <?php foreach($clienti as $c): ?>
                    <option value="<?= $c['id_cliente'] ?>">
                        <?= htmlspecialchars($c['cognome'] . ' ' . $c['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit">Inserisci</button>
        </div>
    </form>
</body>
</html>
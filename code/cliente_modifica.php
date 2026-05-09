<?php
require_once 'config.php';
checkLogin();

$id = $_GET['id'] ?? 0;
$errore = '';
$successo = '';

$stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente non trovato');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    
    if (empty($nome) || empty($cognome) || empty($email)) {
        $errore = 'Compila tutti i campi obbligatori';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE cliente SET nome=?, cognome=?, email=?, telefono=? 
                                   WHERE id_cliente=?");
            $stmt->execute([$nome, $cognome, $email, $telefono, $id]);
            $successo = 'Cliente modificato con successo';
            
            // Ricarica dati
            $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
            $stmt->execute([$id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $errore = 'Errore modifica: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifica Cliente</title>
</head>
<body>
    <h1>Modifica Cliente</h1>
    <a href="clienti_lista.php">Torna alla lista</a>
    
    <?php if ($errore): ?>
        <p style="color: red;"><?= htmlspecialchars($errore) ?></p>
    <?php endif; ?>
    
    <?php if ($successo): ?>
        <p style="color: green;"><?= htmlspecialchars($successo) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Nome*:</label><br>
            <input type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
        </div>
        <div>
            <label>Cognome*:</label><br>
            <input type="text" name="cognome" value="<?= htmlspecialchars($cliente['cognome']) ?>" required>
        </div>
        <div>
            <label>Email*:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
        </div>
        <div>
            <label>Telefono:</label><br>
            <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>">
        </div>
        <div>
            <button type="submit">Salva Modifiche</button>
        </div>
    </form>
</body>
</html>
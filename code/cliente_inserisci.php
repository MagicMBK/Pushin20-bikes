<?php
require_once 'config.php';
checkLogin();

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cognome = $_POST['cognome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($nome) || empty($cognome) || empty($email) || empty($username) || empty($password)) {
        $errore = 'Compila tutti i campi obbligatori';
    } else {
        try {
            $pdo->beginTransaction();
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) 
                                   VALUES (?, ?, 'cliente', 1)");
            $stmt->execute([$username, $password_hash]);
            $id_utente = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO cliente (nome, cognome, email, telefono, id_utente) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $cognome, $email, $telefono, $id_utente]);
            
            $pdo->commit();
            $successo = 'Cliente inserito con successo';
        } catch(PDOException $e) {
            $pdo->rollBack();
            $errore = 'Errore inserimento: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inserisci Cliente</title>
</head>
<body>
    <h1>Inserisci Nuovo Cliente</h1>
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
            <input type="text" name="nome" required>
        </div>
        <div>
            <label>Cognome*:</label><br>
            <input type="text" name="cognome" required>
        </div>
        <div>
            <label>Email*:</label><br>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Telefono:</label><br>
            <input type="text" name="telefono">
        </div>
        <div>
            <label>Username*:</label><br>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Password*:</label><br>
            <input type="password" name="password" required>
        </div>
        <div>
            <button type="submit">Inserisci</button>
        </div>
    </form>
</body>
</html>
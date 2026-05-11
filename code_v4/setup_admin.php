<?php
require 'config.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM utente WHERE ruolo='admin' AND attivo=1");
if ((int) $stmt->fetchColumn() > 0) {
    exit('<div style="color:red;font-family:sans-serif;max-width:600px;margin:40px auto">
        Esiste già un account admin attivo.<br>
        Questo script è disabilitato per sicurezza.<br>
        <b>Elimina questo file dal server.</b>
    </div>');
}

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma = $_POST['conferma'] ?? '';

    if (empty($username) || empty($password)) {
        $errore = 'Username e password sono obbligatori.';
    } elseif ($password !== $conferma) {
        $errore = 'Le password non coincidono.';
    } elseif (strlen($password) < 8) {
        $errore = 'La password deve essere di almeno 8 caratteri.';
    } else {
        try {
            $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) VALUES (?, ?, 'admin', 1)")
                ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $successo = true;
        } catch (PDOException $e) {
            $errore = ($e->getCode() == 23000) ? 'Username già in uso.' : $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Setup Admin - Pushin20 Bikes</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h1>Setup Admin</h1>
        <p style="color:orange"><strong>Attenzione:</strong> elimina questo file dal server dopo aver creato l'account
            admin.</p>

        <?php if ($errore): ?>
            <div class="error"><?= htmlspecialchars($errore) ?></div><?php endif; ?>

        <?php if (!empty($successo)): ?>
            <div class="success">
                Account admin creato con successo!<br>
                <strong>Vai al <a href="login.php">login</a> ed elimina immediatamente questo file.</strong>
            </div>
        <?php else: ?>
            <form method="POST">
                <div>
                    <label>Username admin *</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label>Password * (min. 8 caratteri)</label>
                    <input type="password" name="password" required>
                </div>
                <div>
                    <label>Conferma Password *</label>
                    <input type="password" name="conferma" required>
                </div>
                <button type="submit" style="background:darkred;color:white">Crea Account Admin</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>


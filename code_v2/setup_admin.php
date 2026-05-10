<?php
/**
 * SETUP ADMIN - USO UNA TANTUM
 * Crea il primo account amministratore.
 * !! ELIMINARE QUESTO FILE DOPO L'USO !!
 */
require_once 'config.php';

// Blocca se esiste già almeno un admin attivo
$stmt = $pdo->query("SELECT COUNT(*) FROM utente WHERE ruolo='admin' AND attivo=1");
if ((int)$stmt->fetchColumn() > 0) {
    die('<p style="color:red;font-family:sans-serif">
         ⚠️ Esiste già un account admin attivo.<br>
         Questo script è disabilitato per sicurezza.<br>
         <b>Elimina questo file dal server.</b>
         </p>');
}

$errore  = '';
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
<head><meta charset="UTF-8"><title>Setup Admin - Pushin20 Bikes</title></head>
<body style="font-family:sans-serif;max-width:400px;margin:40px auto">
    <h1>⚙️ Setup Admin</h1>
    <p style="color:orange"><strong>Attenzione:</strong> elimina questo file dal server dopo aver creato l'account admin.</p>

    <?php if ($errore): ?><p style="color:red;"><?= htmlspecialchars($errore) ?></p><?php endif; ?>

    <?php if (!empty($successo)): ?>
        <p style="color:green;font-size:1.2em">✅ Account admin creato con successo!<br>
        <strong>Vai al <a href="login.php">login</a> ed elimina immediatamente questo file.</strong></p>
    <?php else: ?>
    <form method="POST">
        <div><label>Username admin *:</label><br><input type="text" name="username" required style="width:100%"></div><br>
        <div><label>Password * (min. 8 caratteri):</label><br><input type="password" name="password" required style="width:100%"></div><br>
        <div><label>Conferma Password *:</label><br><input type="password" name="conferma" required style="width:100%"></div><br>
        <button type="submit" style="background:darkred;color:white;padding:8px 20px;border:none;cursor:pointer">Crea Account Admin</button>
    </form>
    <?php endif; ?>
</body>
</html>

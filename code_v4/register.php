<?php
require 'config.php';
if (isLogged()) {
    header('Location: dashboard.php');
    exit;
}

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma = $_POST['conferma'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (empty($username) || empty($password) || empty($nome) || empty($cognome) || empty($email)) {
        $errore = 'Compila tutti i campi obbligatori.';
    } elseif ($password !== $conferma) {
        $errore = 'Le password non coincidono.';
    } elseif (strlen($password) < 6) {
        $errore = 'La password deve essere di almeno 6 caratteri.';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) VALUES (?, ?, 'cliente', 1)");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            $id_utente = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO cliente (nome, cognome, email, telefono, id_utente) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $cognome, $email, $telefono ?: null, $id_utente]);
            $pdo->commit();

            $successo = true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errore = ($e->getCode() == 23000) ? 'Username o email già in uso.' : 'Errore: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Registrazione - Pushin20 Bikes</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h1>Crea Account Cliente</h1>
        <p><a href="login.php">Hai già un account? Accedi</a></p>

        <?php if ($errore): ?>
            <div class="error"><?= htmlspecialchars($errore) ?></div><?php endif; ?>

        <?php if (!empty($successo)): ?>
            <div class="success">Registrazione completata! <a href="login.php">Accedi ora</a>.</div>
        <?php else: ?>
            <form method="POST">
                <h3>Credenziali</h3>
                <div>
                    <label>Username *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Password * (min. 6 caratteri)</label>
                    <input type="password" name="password" required>
                </div>
                <div>
                    <label>Conferma Password *</label>
                    <input type="password" name="conferma" required>
                </div>

                <h3>Dati Personali</h3>
                <div>
                    <label>Nome *</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Cognome *</label>
                    <input type="text" name="cognome" value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Telefono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                </div>
                <button type="submit">Registrati</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>


<?php
require_once 'config.php';
if (isLogged()) { header('Location: dashboard.php'); exit; }

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errore = 'Inserisci username e password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utente WHERE username = ? AND attivo = 1");
        $stmt->execute([$username]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u && password_verify($password, $u['password_hash'])) {
            $_SESSION['id_utente'] = $u['id_utente'];
            $_SESSION['username']  = $u['username'];
            $_SESSION['ruolo']     = $u['ruolo'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errore = 'Credenziali non valide.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Login - Pushin20 Bikes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Pushin20 Bikes</h1>
    <h2>Login</h2>

    <?php if ($errore): ?>
        <div class="error"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Accedi</button>
    </form>

    <hr>
    <p>Non hai un account? <a href="register.php">Registrati</a></p>
</div>

</body>
</html>
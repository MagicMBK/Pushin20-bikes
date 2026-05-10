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
<head><meta charset="UTF-8"><title>Login - Pushin20 Bikes</title></head>
<body>
    <h1>Pushin20 Bikes – Login</h1>

    <?php if ($errore): ?>
        <p style="color:red;"><?= htmlspecialchars($errore) ?></p>
    <?php endif; ?>

    <form method="POST">
        <div><label>Username:</label><br><input type="text" name="username" required></div>
        <div><label>Password:</label><br><input type="password" name="password" required></div>
        <div><button type="submit">Accedi</button></div>
    </form>

    <hr>
    <p>Non hai un account? <a href="register.php">Registrati come cliente</a></p>
</body>
</html>

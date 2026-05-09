<?php
require_once 'config.php';

$errore = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $errore = 'Inserisci username e password';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utente WHERE username = ? AND attivo = 1");
        $stmt->execute([$username]);
        $utente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($utente && password_verify($password, $utente['password_hash'])) {
            $_SESSION['id_utente'] = $utente['id_utente'];
            $_SESSION['username'] = $utente['username'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errore = 'Credenziali non valide';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Pushin20 Bikes</title>
</head>
<body>
    <h1>Login Officina Moto</h1>
    
    <?php if ($errore): ?>
        <p style="color: red;"><?= htmlspecialchars($errore) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Username:</label><br>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Password:</label><br>
            <input type="password" name="password" required>
        </div>
        <div>
            <button type="submit">Accedi</button>
        </div>
    </form>
</body>
</html>
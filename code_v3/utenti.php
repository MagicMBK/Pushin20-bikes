<?php
require_once 'config.php';
checkAdmin();

$errore  = '';
$successo = '';
$azione  = $_GET['azione'] ?? 'lista';
$id      = (int)($_GET['id'] ?? 0);

if ($azione === 'elimina' && $id) {
    if ($id === (int)$_SESSION['id_utente']) {
        $errore = 'Non puoi eliminare il tuo account.';
        $azione = 'lista';
    } else {
        try {
            $pdo->prepare("DELETE FROM utente WHERE id_utente = ?")->execute([$id]);
            header('Location: utenti.php?ok=eliminato'); exit;
        } catch (PDOException $e) {
            $errore = 'Impossibile eliminare (utente associato a dati): ' . $e->getMessage();
            $azione = 'lista';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $ruolo    = $_POST['ruolo'] ?? '';
    $attivo   = isset($_POST['attivo']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    $edit_id  = (int)($_POST['edit_id'] ?? 0);

    if (empty($username) || empty($ruolo)) {
        $errore = 'Username e ruolo sono obbligatori.';
    } elseif (!$edit_id && empty($password)) {
        $errore = 'La password è obbligatoria per i nuovi utenti.';
    } else {
        try {
            if ($edit_id) {
                if (!empty($password)) {
                    $pdo->prepare("UPDATE utente SET username=?, password_hash=?, ruolo=?, attivo=? WHERE id_utente=?")
                        ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $ruolo, $attivo, $edit_id]);
                } else {
                    $pdo->prepare("UPDATE utente SET username=?, ruolo=?, attivo=? WHERE id_utente=?")
                        ->execute([$username, $ruolo, $attivo, $edit_id]);
                }
                header('Location: utenti.php?ok=modificato'); exit;
            } else {
                $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) VALUES (?, ?, ?, ?)")
                    ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $ruolo, $attivo]);
                header('Location: utenti.php?ok=creato'); exit;
            }
        } catch (PDOException $e) {
            $errore = ($e->getCode() == 23000) ? 'Username già in uso.' : $e->getMessage();
        }
    }
    $azione = $edit_id ? 'modifica' : 'nuovo';
}

$u = null;
if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM utente WHERE id_utente = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) { header('Location: utenti.php'); exit; }
}

$utenti = [];
if ($azione === 'lista') {
    $utenti = $pdo->query("
        SELECT u.*, COALESCE(CONCAT(c.nome,' ',c.cognome), CONCAT(m.nome,' ',m.cognome), '-') AS nome_completo
        FROM utente u
        LEFT JOIN cliente   c ON c.id_utente = u.id_utente
        LEFT JOIN meccanico m ON m.id_utente = u.id_utente
        ORDER BY u.ruolo, u.username
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Utenti - Pushin20 Bikes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php">← Dashboard</a>
    <h1>Gestione Utenti</h1>

    <?php if ($errore): ?><div class="error"><?= htmlspecialchars($errore) ?></div><?php endif; ?>
    <?php if (!empty($_GET['ok'])): ?><div class="success">Operazione completata.</div><?php endif; ?>

    <?php if ($azione === 'lista'): ?>
        <p><a href="utenti.php?azione=nuovo">+ Nuovo Utente</a></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Ruolo</th>
                    <th>Nome</th>
                    <th>Attivo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $row): ?>
                <tr>
                    <td><?= $row['id_utente'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['ruolo']) ?></td>
                    <td><?= htmlspecialchars($row['nome_completo']) ?></td>
                    <td><?= $row['attivo'] ? 'Sì' : 'No' ?></td>
                    <td>
                        <a href="utenti.php?azione=modifica&id=<?= $row['id_utente'] ?>">Modifica</a> |
                        <a href="utenti.php?azione=elimina&id=<?= $row['id_utente'] ?>"
                           onclick="return confirm('Eliminare <?= htmlspecialchars($row['username']) ?>?')">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <h2><?= $azione === 'modifica' ? 'Modifica Utente' : 'Nuovo Utente' ?></h2>
        <a href="utenti.php">← Torna alla lista</a>

        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $u['id_utente'] ?? 0 ?>">

            <div>
                <label>Username *</label>
                <input type="text" name="username" value="<?= htmlspecialchars($u['username'] ?? '') ?>" required>
            </div>
            <div>
                <label>Password <?= $azione === 'modifica' ? '(lascia vuoto per non cambiarla)' : '*' ?></label>
                <input type="password" name="password" <?= $azione !== 'modifica' ? 'required' : '' ?>>
            </div>
            <div>
                <label>Ruolo *</label>
                <select name="ruolo" required>
                    <?php foreach (['admin','meccanico','cliente'] as $r): ?>
                    <option value="<?= $r ?>" <?= isset($u) && $u['ruolo'] === $r ? 'selected' : '' ?>><?= $r ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>
                    <input type="checkbox" name="attivo" <?= !isset($u) || $u['attivo'] ? 'checked' : '' ?>> Attivo
                </label>
            </div>
            <button type="submit"><?= $azione === 'modifica' ? 'Salva Modifiche' : 'Crea Utente' ?></button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
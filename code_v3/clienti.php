<?php
require_once 'config.php';
checkRole(['admin', 'meccanico']);

$errore  = '';
$azione  = $_GET['azione'] ?? 'lista';
$id      = (int)($_GET['id'] ?? 0);

// Solo admin può modificare/eliminare
$solo_lettura = ($_SESSION['ruolo'] !== 'admin');

// ELIMINA (solo admin)
if ($azione === 'elimina' && $id && !$solo_lettura) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id_utente FROM cliente WHERE id_cliente = ?");
        $stmt->execute([$id]);
        $cli = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo->prepare("DELETE FROM cliente WHERE id_cliente = ?")->execute([$id]);
        if ($cli) $pdo->prepare("DELETE FROM utente WHERE id_utente = ?")->execute([$cli['id_utente']]);
        $pdo->commit();
        header('Location: clienti.php?ok=1'); exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errore = 'Impossibile eliminare: ' . $e->getMessage();
        $azione = 'lista';
    }
}

// SALVA (solo admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$solo_lettura) {
    $nome     = trim($_POST['nome'] ?? '');
    $cognome  = trim($_POST['cognome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $edit_id  = (int)($_POST['edit_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($nome) || empty($cognome) || empty($email)) {
        $errore = 'Nome, cognome ed email sono obbligatori.';
    } elseif (!$edit_id && (empty($username) || empty($password))) {
        $errore = 'Username e password sono obbligatori per un nuovo cliente.';
    } else {
        try {
            if ($edit_id) {
                $pdo->prepare("UPDATE cliente SET nome=?, cognome=?, email=?, telefono=? WHERE id_cliente=?")
                    ->execute([$nome, $cognome, $email, $telefono ?: null, $edit_id]);
                header('Location: clienti.php?ok=1'); exit;
            } else {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) VALUES (?, ?, 'cliente', 1)")
                    ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
                $id_utente = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO cliente (nome, cognome, email, telefono, id_utente) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$nome, $cognome, $email, $telefono ?: null, $id_utente]);
                $pdo->commit();
                header('Location: clienti.php?ok=1'); exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errore = ($e->getCode() == 23000) ? 'Username o email già in uso.' : $e->getMessage();
        }
    }
    $azione = $edit_id ? 'modifica' : 'nuovo';
}

// Carica cliente per modifica
$cli = null;
if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM cliente c JOIN utente u ON u.id_utente=c.id_utente WHERE c.id_cliente=?");
    $stmt->execute([$id]);
    $cli = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cli) { header('Location: clienti.php'); exit; }
}

// Lista
$clienti = [];
if ($azione === 'lista') {
    $clienti = $pdo->query("SELECT c.*, u.username FROM cliente c JOIN utente u ON c.id_utente=u.id_utente ORDER BY c.cognome, c.nome")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Clienti - Pushin20 Bikes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php">← Dashboard</a>
    <h1>Gestione Clienti</h1>

    <?php if ($errore): ?>
        <div class="error"><?= htmlspecialchars($errore) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['ok'])): ?>
        <div class="success">Operazione completata.</div>
    <?php endif; ?>

    <?php if ($azione === 'lista'): ?>

        <?php if (!$solo_lettura): ?>
            <p><a href="clienti.php?azione=nuovo">+ Nuovo Cliente</a></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Email</th>
                    <th>Telefono</th>
                    <th>Username</th>
                    <?php if (!$solo_lettura): ?><th>Azioni</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clienti as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nome']) ?></td>
                    <td><?= htmlspecialchars($c['cognome']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['telefono'] ?? '–') ?></td>
                    <td><?= htmlspecialchars($c['username']) ?></td>
                    <?php if (!$solo_lettura): ?>
                    <td>
                        <a href="clienti.php?azione=modifica&id=<?= $c['id_cliente'] ?>">Modifica</a> |
                        <a href="clienti.php?azione=elimina&id=<?= $c['id_cliente'] ?>"
                           onclick="return confirm('Eliminare <?= htmlspecialchars($c['nome'].' '.$c['cognome']) ?>?')">Elimina</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <h2><?= $azione === 'modifica' ? 'Modifica Cliente' : 'Nuovo Cliente' ?></h2>
        <a href="clienti.php">← Torna alla lista</a>

        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $cli['id_cliente'] ?? 0 ?>">

            <?php if ($azione === 'nuovo'): ?>
            <div>
                <label>Username *</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Password *</label>
                <input type="password" name="password" required>
            </div>
            <?php endif; ?>

            <div>
                <label>Nome *</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($cli['nome'] ?? '') ?>" required>
            </div>
            <div>
                <label>Cognome *</label>
                <input type="text" name="cognome" value="<?= htmlspecialchars($cli['cognome'] ?? '') ?>" required>
            </div>
            <div>
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($cli['email'] ?? '') ?>" required>
            </div>
            <div>
                <label>Telefono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($cli['telefono'] ?? '') ?>">
            </div>

            <button type="submit"><?= $azione === 'modifica' ? 'Salva Modifiche' : 'Crea Cliente' ?></button>
        </form>

    <?php endif; ?>
</div>

</body>
</html>
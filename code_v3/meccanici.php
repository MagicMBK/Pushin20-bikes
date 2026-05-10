<?php
require_once 'config.php';
checkAdmin();
$errore = '';
$azione = $_GET['azione'] ?? 'lista';
$id     = (int)($_GET['id'] ?? 0);

if ($azione === 'elimina' && $id) {
    try {
        $pdo->prepare("DELETE FROM meccanico WHERE id_meccanico = ?")->execute([$id]);
        header('Location: meccanici.php?ok=1'); exit;
    } catch (PDOException $e) {
        $errore = 'Impossibile eliminare: ' . $e->getMessage();
        $azione = 'lista';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome             = trim($_POST['nome'] ?? '');
    $cognome          = trim($_POST['cognome'] ?? '');
    $specializzazione = trim($_POST['specializzazione'] ?? '');
    $costo_orario     = (int)($_POST['costo_orario'] ?? 0);
    $edit_id          = (int)($_POST['edit_id'] ?? 0);
    $id_utente        = (int)($_POST['id_utente'] ?? 0);
    $username         = trim($_POST['username'] ?? '');
    $password         = $_POST['password'] ?? '';

    if (empty($nome) || empty($cognome) || !$costo_orario) {
        $errore = 'Nome, cognome e costo orario sono obbligatori.';
    } elseif (!$edit_id && (!$id_utente && (empty($username) || empty($password)))) {
        $errore = 'Seleziona un utente esistente oppure crea un account nuovo.';
    } else {
        try {
            if ($edit_id) {
                $pdo->prepare("UPDATE meccanico SET nome=?, cognome=?, specializzazione=?, costo_orario=? WHERE id_meccanico=?")
                    ->execute([$nome, $cognome, $specializzazione ?: null, $costo_orario, $edit_id]);
                header('Location: meccanici.php?ok=1'); exit;
            } else {
                $pdo->beginTransaction();
                if (!$id_utente) {
                    $pdo->prepare("INSERT INTO utente (username, password_hash, ruolo, attivo) VALUES (?, ?, 'meccanico', 1)")
                        ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
                    $id_utente = $pdo->lastInsertId();
                }
                $pdo->prepare("INSERT INTO meccanico (nome, cognome, specializzazione, costo_orario, id_utente) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$nome, $cognome, $specializzazione ?: null, $costo_orario, $id_utente]);
                $pdo->commit();
                header('Location: meccanici.php?ok=1'); exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errore = ($e->getCode() == 23000) ? 'Username già in uso.' : $e->getMessage();
        }
    }
    $azione = $edit_id ? 'modifica' : 'nuovo';
}

$mec = null;
if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("SELECT m.*, u.username FROM meccanico m JOIN utente u ON u.id_utente=m.id_utente WHERE m.id_meccanico=?");
    $stmt->execute([$id]);
    $mec = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mec) { header('Location: meccanici.php'); exit; }
}

$utenti_liberi = [];
if ($azione === 'nuovo') {
    $utenti_liberi = $pdo->query("
        SELECT id_utente, username FROM utente
        WHERE ruolo='meccanico' AND id_utente NOT IN (SELECT id_utente FROM meccanico)
        ORDER BY username
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$meccanici = [];
if ($azione === 'lista') {
    $meccanici = $pdo->query("
        SELECT m.*, u.username, u.attivo FROM meccanico m
        JOIN utente u ON u.id_utente=m.id_utente
        ORDER BY m.cognome, m.nome
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Meccanici - Pushin20 Bikes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <a href="dashboard.php">← Dashboard</a>
    <h1>Gestione Meccanici</h1>

    <?php if ($errore): ?><div class="error"><?= htmlspecialchars($errore) ?></div><?php endif; ?>
    <?php if (!empty($_GET['ok'])): ?><div class="success">Operazione completata.</div><?php endif; ?>

    <?php if ($azione === 'lista'): ?>
        <p><a href="meccanici.php?azione=nuovo">+ Nuovo Meccanico</a></p>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cognome</th>
                    <th>Specializzazione</th>
                    <th>€/h</th>
                    <th>Username</th>
                    <th>Attivo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meccanici as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nome']) ?></td>
                    <td><?= htmlspecialchars($m['cognome']) ?></td>
                    <td><?= htmlspecialchars($m['specializzazione'] ?? '–') ?></td>
                    <td><?= $m['costo_orario'] ?></td>
                    <td><?= htmlspecialchars($m['username']) ?></td>
                    <td><?= $m['attivo'] ? 'Sì' : 'No' ?></td>
                    <td>
                        <a href="meccanici.php?azione=modifica&id=<?= $m['id_meccanico'] ?>">Modifica</a> |
                        <a href="meccanici.php?azione=elimina&id=<?= $m['id_meccanico'] ?>"
                           onclick="return confirm('Eliminare <?= htmlspecialchars($m['nome'].' '.$m['cognome']) ?>?')">Elimina</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <h2><?= $azione === 'modifica' ? 'Modifica Meccanico' : 'Nuovo Meccanico' ?></h2>
        <a href="meccanici.php">← Torna alla lista</a>
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $mec['id_meccanico'] ?? 0 ?>">

            <?php if ($azione === 'nuovo'): ?>
            <fieldset>
                <legend>Account utente</legend>
                <?php if ($utenti_liberi): ?>
                <div>
                    <label>Seleziona un utente meccanico esistente:</label>
                    <select name="id_utente">
                        <option value="0">– crea nuovo account –</option>
                        <?php foreach ($utenti_liberi as $u): ?>
                        <option value="<?= $u['id_utente'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p>Oppure crea un account nuovo:</p>
                <?php endif; ?>
                <div>
                    <label>Username</label>
                    <input type="text" name="username">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password">
                </div>
            </fieldset>
            <?php else: ?>
                <p>Utente associato: <strong><?= htmlspecialchars($mec['username']) ?></strong></p>
            <?php endif; ?>

            <div>
                <label>Nome *</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($mec['nome'] ?? '') ?>" required>
            </div>
            <div>
                <label>Cognome *</label>
                <input type="text" name="cognome" value="<?= htmlspecialchars($mec['cognome'] ?? '') ?>" required>
            </div>
            <div>
                <label>Specializzazione</label>
                <input type="text" name="specializzazione" value="<?= htmlspecialchars($mec['specializzazione'] ?? '') ?>">
            </div>
            <div>
                <label>Costo orario (€) *</label>
                <input type="number" name="costo_orario" value="<?= $mec['costo_orario'] ?? '' ?>" min="1" required>
            </div>
            <button type="submit"><?= $azione === 'modifica' ? 'Salva Modifiche' : 'Crea Meccanico' ?></button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
<?php
require_once 'config.php';
checkAdmin();

$errore = '';
$azione = $_GET['azione'] ?? 'lista';
$id     = (int)($_GET['id'] ?? 0);

// ELIMINA
if ($azione === 'elimina' && $id) {
    try {
        $pdo->prepare("DELETE FROM fornitore WHERE id_fornitore=?")->execute([$id]);
        header('Location: fornitori_lista.php?ok=1'); exit;
    } catch (PDOException $e) {
        $errore = 'Impossibile eliminare (fornitore in uso).';
        $azione = 'lista';
    }
}

// SALVA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ragione  = trim($_POST['ragione_sociale'] ?? '');
    $piva     = trim($_POST['partita_iva'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $edit_id  = (int)($_POST['edit_id'] ?? 0);

    if (empty($ragione) || empty($piva)) {
        $errore = 'Ragione sociale e partita IVA sono obbligatorie.';
    } else {
        try {
            if ($edit_id) {
                $pdo->prepare("UPDATE fornitore SET ragione_sociale=?, partita_iva=?, email=? WHERE id_fornitore=?")
                    ->execute([$ragione, $piva, $email ?: null, $edit_id]);
            } else {
                $pdo->prepare("INSERT INTO fornitore (ragione_sociale, partita_iva, email) VALUES (?,?,?)")
                    ->execute([$ragione, $piva, $email ?: null]);
            }
            header('Location: fornitori_lista.php?ok=1'); exit;
        } catch (PDOException $e) {
            $errore = ($e->getCode() == 23000) ? 'Partita IVA già presente.' : $e->getMessage();
        }
    }
    $azione = $edit_id ? 'modifica' : 'nuovo';
}

// Carica fornitore per modifica
$forn = null;
if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM fornitore WHERE id_fornitore=?");
    $stmt->execute([$id]);
    $forn = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$forn) { header('Location: fornitori_lista.php'); exit; }
}

// Lista
$lista = [];
if ($azione === 'lista') {
    $lista = $pdo->query("SELECT * FROM fornitore ORDER BY ragione_sociale")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Fornitori</title></head>
<body>
<a href="dashboard.php">← Dashboard</a>
<h1>Gestione Fornitori</h1>

<?php if ($errore): ?><p style="color:red"><?= htmlspecialchars($errore) ?></p><?php endif; ?>
<?php if (!empty($_GET['ok'])): ?><p style="color:green">Operazione completata.</p><?php endif; ?>

<?php if ($azione === 'lista'): ?>

    <p><a href="fornitori_lista.php?azione=nuovo">+ Nuovo Fornitore</a></p>
    <table border="1" cellpadding="6">
        <tr><th>Ragione Sociale</th><th>Partita IVA</th><th>Email</th><th>Azioni</th></tr>
        <?php foreach ($lista as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['ragione_sociale']) ?></td>
            <td><?= htmlspecialchars($f['partita_iva']) ?></td>
            <td><?= htmlspecialchars($f['email'] ?? '–') ?></td>
            <td>
                <a href="fornitori_lista.php?azione=modifica&id=<?= $f['id_fornitore'] ?>">Modifica</a> |
                <a href="fornitori_lista.php?azione=elimina&id=<?= $f['id_fornitore'] ?>"
                   onclick="return confirm('Eliminare <?= htmlspecialchars($f['ragione_sociale']) ?>?')">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

<?php else: ?>

    <h2><?= $azione === 'modifica' ? 'Modifica' : 'Nuovo' ?> Fornitore</h2>
    <a href="fornitori_lista.php">← Torna alla lista</a>
    <form method="POST" style="margin-top:10px">
        <input type="hidden" name="edit_id" value="<?= $forn['id_fornitore'] ?? 0 ?>">
        <div><label>Ragione Sociale *:</label><br>
            <input type="text" name="ragione_sociale" value="<?= htmlspecialchars($forn['ragione_sociale'] ?? '') ?>" required></div>
        <div><label>Partita IVA *:</label><br>
            <input type="text" name="partita_iva" value="<?= htmlspecialchars($forn['partita_iva'] ?? '') ?>" required></div>
        <div><label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($forn['email'] ?? '') ?>"></div>
        <div style="margin-top:8px"><button type="submit">Salva</button></div>
    </form>

<?php endif; ?>
</body>
</html>

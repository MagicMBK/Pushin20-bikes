<?php
require_once 'config.php';
checkRole(['cliente']);

// Recupera id_cliente
$stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_utente = ?");
$stmt->execute([$_SESSION['id_utente']]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cli) die('Profilo cliente non trovato.');
$id_cliente = $cli['id_cliente'];

$errore  = '';
$azione  = $_GET['azione'] ?? 'lista';
$id      = (int)($_GET['id'] ?? 0);

// ELIMINA
if ($azione === 'elimina' && $id) {
    // Verifica che la moto appartenga al cliente
    $stmt = $pdo->prepare("SELECT id_moto FROM moto WHERE id_moto=? AND id_cliente=?");
    $stmt->execute([$id, $id_cliente]);
    if ($stmt->fetch()) {
        try {
            $pdo->prepare("DELETE FROM moto WHERE id_moto=?")->execute([$id]);
            header('Location: mie_moto.php?ok=1'); exit;
        } catch (PDOException $e) {
            $errore = 'Impossibile eliminare (esistono interventi collegati): ' . $e->getMessage();
        }
    }
    $azione = 'lista';
}

// SALVA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targa   = strtoupper(trim($_POST['targa'] ?? ''));
    $marca   = trim($_POST['marca'] ?? '');
    $modello = trim($_POST['modello'] ?? '');
    $anno    = (int)($_POST['anno'] ?? 0);
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($targa) || empty($marca) || empty($modello) || !$anno) {
        $errore = 'Compila tutti i campi.';
    } else {
        try {
            if ($edit_id) {
                // Verifica proprietà
                $stmt = $pdo->prepare("SELECT id_moto FROM moto WHERE id_moto=? AND id_cliente=?");
                $stmt->execute([$edit_id, $id_cliente]);
                if (!$stmt->fetch()) die('Accesso negato.');
                $pdo->prepare("UPDATE moto SET targa=?, marca=?, modello=?, anno=? WHERE id_moto=?")
                    ->execute([$targa, $marca, $modello, $anno, $edit_id]);
            } else {
                $pdo->prepare("INSERT INTO moto (targa, marca, modello, anno, id_cliente) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$targa, $marca, $modello, $anno, $id_cliente]);
            }
            header('Location: mie_moto.php?ok=1'); exit;
        } catch (PDOException $e) {
            $errore = ($e->getCode() == 23000) ? 'Targa già registrata.' : $e->getMessage();
        }
    }
    $azione = $edit_id ? 'modifica' : 'nuova';
}

// Carica moto per modifica
$moto = null;
if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM moto WHERE id_moto=? AND id_cliente=?");
    $stmt->execute([$id, $id_cliente]);
    $moto = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$moto) { header('Location: mie_moto.php'); exit; }
}

// Lista moto del cliente
$lista = [];
if ($azione === 'lista') {
    $stmt = $pdo->prepare("SELECT * FROM moto WHERE id_cliente=? ORDER BY marca, modello");
    $stmt->execute([$id_cliente]);
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>Le mie Moto - Pushin20 Bikes</title></head>
<body>
    <a href="dashboard.php">← Dashboard</a>
    <h1>Le mie Moto</h1>

    <?php if ($errore): ?><p style="color:red;"><?= htmlspecialchars($errore) ?></p><?php endif; ?>
    <?php if (!empty($_GET['ok'])): ?><p style="color:green;">Operazione completata.</p><?php endif; ?>

    <?php if ($azione === 'lista'): ?>

        <a href="mie_moto.php?azione=nuova">+ Aggiungi Moto</a>
        <?php if (empty($lista)): ?>
            <p>Nessuna moto registrata.</p>
        <?php else: ?>
        <table border="1" cellpadding="6">
            <tr><th>Targa</th><th>Marca</th><th>Modello</th><th>Anno</th><th>Azioni</th></tr>
            <?php foreach ($lista as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['targa']) ?></td>
                <td><?= htmlspecialchars($m['marca']) ?></td>
                <td><?= htmlspecialchars($m['modello']) ?></td>
                <td><?= $m['anno'] ?></td>
                <td>
                    <a href="mie_moto.php?azione=modifica&id=<?= $m['id_moto'] ?>">Modifica</a> |
                    <a href="mie_moto.php?azione=elimina&id=<?= $m['id_moto'] ?>"
                       onclick="return confirm('Eliminare questa moto?')">Elimina</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

    <?php else: ?>

        <h2><?= $azione === 'modifica' ? 'Modifica Moto' : 'Aggiungi Moto' ?></h2>
        <a href="mie_moto.php">← Torna alla lista</a>
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= $moto['id_moto'] ?? 0 ?>">
            <div><label>Targa *:</label><br><input type="text" name="targa" value="<?= htmlspecialchars($moto['targa'] ?? '') ?>" required maxlength="10"></div>
            <div><label>Marca *:</label><br><input type="text" name="marca" value="<?= htmlspecialchars($moto['marca'] ?? '') ?>" required></div>
            <div><label>Modello *:</label><br><input type="text" name="modello" value="<?= htmlspecialchars($moto['modello'] ?? '') ?>" required></div>
            <div><label>Anno *:</label><br><input type="number" name="anno" value="<?= $moto['anno'] ?? date('Y') ?>" min="1900" max="<?= date('Y') ?>" required></div>
            <div><button type="submit"><?= $azione === 'modifica' ? 'Salva Modifiche' : 'Aggiungi' ?></button></div>
        </form>

    <?php endif; ?>
</body>
</html>

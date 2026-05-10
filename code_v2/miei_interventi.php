<?php
require_once 'config.php';
checkRole(['cliente']);

$stmt = $pdo->prepare("SELECT id_cliente FROM cliente WHERE id_utente = ?");
$stmt->execute([$_SESSION['id_utente']]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cli) die('Profilo cliente non trovato.');
$id_cliente = $cli['id_cliente'];

$errore  = '';
$azione  = $_GET['azione'] ?? 'lista';
$id      = (int)($_GET['id'] ?? 0);

// RICHIEDI NUOVO INTERVENTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_moto = (int)($_POST['id_moto'] ?? 0);
    $note    = trim($_POST['note'] ?? '');

    // Verifica che la moto appartenga al cliente
    $stmt = $pdo->prepare("SELECT id_moto FROM moto WHERE id_moto=? AND id_cliente=?");
    $stmt->execute([$id_moto, $id_cliente]);
    if (!$stmt->fetch()) {
        $errore = 'Moto non valida.';
    } elseif (!$id_moto) {
        $errore = 'Seleziona una moto.';
    } else {
        try {
            // Il meccanico viene assegnato dall'admin; per ora prendiamo il primo disponibile
            // oppure lasciamo NULL se la colonna lo permettesse — qui usiamo id_meccanico=1
            // come segnaposto: l'admin lo cambierà poi da interventi_lista
            $stmt = $pdo->prepare("
                INSERT INTO intervento (data_ingresso, stato, costo_totale, id_moto, id_meccanico)
                VALUES (CURDATE(), 'attesa', 0,
                        ?,
                        (SELECT id_meccanico FROM meccanico LIMIT 1))
            ");
            $stmt->execute([$id_moto]);
            header('Location: miei_interventi.php?ok=1'); exit;
        } catch (PDOException $e) {
            $errore = 'Errore: ' . $e->getMessage();
        }
    }
    $azione = 'nuovo';
}

// Lista moto del cliente per il form
$mie_moto = [];
if ($azione === 'nuovo') {
    $stmt = $pdo->prepare("SELECT id_moto, targa, marca, modello FROM moto WHERE id_cliente=? ORDER BY marca");
    $stmt->execute([$id_cliente]);
    $mie_moto = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lista interventi
$interventi = [];
if ($azione === 'lista') {
    $stmt = $pdo->prepare("
        SELECT i.*, m.targa, m.marca, m.modello,
               mec.nome AS mec_nome, mec.cognome AS mec_cognome
        FROM intervento i
        JOIN moto m ON i.id_moto = m.id_moto
        JOIN meccanico mec ON i.id_meccanico = mec.id_meccanico
        WHERE m.id_cliente = ?
        ORDER BY i.data_ingresso DESC
    ");
    $stmt->execute([$id_cliente]);
    $interventi = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Dettaglio singolo intervento
$dettaglio = null;
$pezzi_usati = [];
if ($azione === 'dettaglio' && $id) {
    $stmt = $pdo->prepare("
        SELECT i.*, m.targa, m.marca, m.modello,
               mec.nome AS mec_nome, mec.cognome AS mec_cognome
        FROM intervento i
        JOIN moto m ON i.id_moto = m.id_moto
        JOIN meccanico mec ON i.id_meccanico = mec.id_meccanico
        WHERE i.id_intervento=? AND m.id_cliente=?
    ");
    $stmt->execute([$id, $id_cliente]);
    $dettaglio = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dettaglio) { header('Location: miei_interventi.php'); exit; }

    $stmt = $pdo->prepare("
        SELECT up.quantita, up.prezzo_unitario, pr.nome AS pezzo_nome
        FROM utilizzo_pezzo up
        JOIN pezzo_ricambio pr ON pr.id_pezzo = up.id_pezzo
        WHERE up.id_intervento = ?
    ");
    $stmt->execute([$id]);
    $pezzi_usati = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stato_colori = [
    'attesa'      => 'orange',
    'in_corso'    => 'blue',
    'completato'  => 'green',
];
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="UTF-8"><title>I miei Interventi - Pushin20 Bikes</title></head>
<body>
    <a href="dashboard.php">← Dashboard</a>
    <h1>I miei Interventi</h1>

    <?php if ($errore): ?><p style="color:red;"><?= htmlspecialchars($errore) ?></p><?php endif; ?>
    <?php if (!empty($_GET['ok'])): ?><p style="color:green;">Richiesta inviata! L'officina ti contatterà per confermare.</p><?php endif; ?>

    <?php if ($azione === 'lista'): ?>

        <a href="miei_interventi.php?azione=nuovo">+ Richiedi Intervento</a>

        <?php if (empty($interventi)): ?>
            <p>Nessun intervento registrato.</p>
        <?php else: ?>
        <table border="1" cellpadding="6">
            <tr><th>Moto</th><th>Data ingresso</th><th>Data uscita</th><th>Stato</th><th>Meccanico</th><th>Costo</th><th>Dettaglio</th></tr>
            <?php foreach ($interventi as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['marca'].' '.$i['modello'].' ('.$i['targa'].')') ?></td>
                <td><?= $i['data_ingresso'] ?></td>
                <td><?= $i['data_uscita'] ?? '–' ?></td>
                <td style="color:<?= $stato_colori[$i['stato']] ?? 'black' ?>"><strong><?= htmlspecialchars($i['stato']) ?></strong></td>
                <td><?= htmlspecialchars($i['mec_nome'].' '.$i['mec_cognome']) ?></td>
                <td><?= $i['costo_totale'] ? '€ '.number_format($i['costo_totale']/100, 2) : '–' ?></td>
                <td><a href="miei_interventi.php?azione=dettaglio&id=<?= $i['id_intervento'] ?>">Vedi</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

    <?php elseif ($azione === 'nuovo'): ?>

        <h2>Richiedi un Intervento</h2>
        <a href="miei_interventi.php">← Torna alla lista</a>

        <?php if (empty($mie_moto)): ?>
            <p>Non hai moto registrate. <a href="mie_moto.php?azione=nuova">Aggiungi una moto</a> prima di richiedere un intervento.</p>
        <?php else: ?>
        <form method="POST">
            <div>
                <label>Moto *:</label><br>
                <select name="id_moto" required>
                    <option value="">– seleziona –</option>
                    <?php foreach ($mie_moto as $m): ?>
                    <option value="<?= $m['id_moto'] ?>"><?= htmlspecialchars($m['marca'].' '.$m['modello'].' – '.$m['targa']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Note / descrizione problema:</label><br>
                <textarea name="note" rows="4" cols="40"></textarea></div>
            <div><button type="submit">Invia Richiesta</button></div>
        </form>
        <p><small>Il meccanico verrà assegnato dall'officina. Ti contatteremo per confermare la data.</small></p>
        <?php endif; ?>

    <?php elseif ($azione === 'dettaglio' && $dettaglio): ?>

        <h2>Dettaglio Intervento #<?= $id ?></h2>
        <a href="miei_interventi.php">← Torna alla lista</a>
        <table border="1" cellpadding="6">
            <tr><th>Moto</th><td><?= htmlspecialchars($dettaglio['marca'].' '.$dettaglio['modello'].' ('.$dettaglio['targa'].')') ?></td></tr>
            <tr><th>Meccanico</th><td><?= htmlspecialchars($dettaglio['mec_nome'].' '.$dettaglio['mec_cognome']) ?></td></tr>
            <tr><th>Data ingresso</th><td><?= $dettaglio['data_ingresso'] ?></td></tr>
            <tr><th>Data uscita</th><td><?= $dettaglio['data_uscita'] ?? '–' ?></td></tr>
            <tr><th>Stato</th><td style="color:<?= $stato_colori[$dettaglio['stato']] ?? 'black' ?>"><strong><?= htmlspecialchars($dettaglio['stato']) ?></strong></td></tr>
            <tr><th>Costo totale</th><td><?= $dettaglio['costo_totale'] ? '€ '.number_format($dettaglio['costo_totale']/100, 2) : '–' ?></td></tr>
        </table>

        <?php if ($pezzi_usati): ?>
        <h3>Pezzi utilizzati</h3>
        <table border="1" cellpadding="6">
            <tr><th>Pezzo</th><th>Quantità</th><th>Prezzo unitario</th><th>Subtotale</th></tr>
            <?php foreach ($pezzi_usati as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['pezzo_nome']) ?></td>
                <td><?= $p['quantita'] ?></td>
                <td>€ <?= number_format($p['prezzo_unitario']/100, 2) ?></td>
                <td>€ <?= number_format($p['quantita'] * $p['prezzo_unitario']/100, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

    <?php endif; ?>
</body>
</html>

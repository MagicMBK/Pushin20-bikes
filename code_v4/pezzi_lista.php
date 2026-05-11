<?php
require 'config.php';
checkRole(['admin', 'meccanico']);

$lista = $pdo->query("
    SELECT p.codice, p.nome, p.quantita_stock, p.scorta_minima,
           p.prezzo_vendita, f.ragione_sociale
    FROM pezzo_ricambio p
    JOIN fornitore f ON p.id_fornitore = f.id_fornitore
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Pezzi di ricambio</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .ricerca {
            margin: 15px 0;
        }

        .ricerca input {
            padding: 8px;
            width: 300px;
            font-size: 16px;
        }

        th {
            cursor: pointer;
            user-select: none;
        }

        th:hover {
            background: #ddd;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="dashboard.php">← Dashboard</a>
        <h1>Pezzi di ricambio</h1>

        <div class="ricerca">
            <input type="text" id="cerca" placeholder="Cerca pezzo..." onkeyup="filtra()">
        </div>

        <table id="tabella">
            <thead>
                <tr>
                    <th onclick="ordina(0)">Codice ↕</th>
                    <th onclick="ordina(1)">Nome ↕</th>
                    <th onclick="ordina(2)">Fornitore ↕</th>
                    <th onclick="ordina(3)">Stock ↕</th>
                    <th onclick="ordina(4)">Prezzo ↕</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $p): ?>
                    <?php $scarso = $p['quantita_stock'] <= $p['scorta_minima']; ?>
                    <tr <?= $scarso ? 'style="background:#ffe0e0"' : '' ?>>
                        <td><?= htmlspecialchars($p['codice']) ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['ragione_sociale']) ?></td>
                        <td><?= $p['quantita_stock'] ?></td>
                        <td>€ <?= number_format($p['prezzo_vendita'] / 100, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function filtra() {
            let input = document.getElementById('cerca').value.toLowerCase();
            let righe = document.querySelectorAll('#tabella tbody tr');

            righe.forEach(riga => {
                let testo = riga.textContent.toLowerCase();
                riga.style.display = testo.includes(input) ? '' : 'none';
            });
        }

        function ordina(colonna) {
            let tabella = document.getElementById('tabella');
            let righe = Array.from(tabella.querySelectorAll('tbody tr'));

            righe.sort((a, b) => {
                let valA = a.cells[colonna].textContent.trim();
                let valB = b.cells[colonna].textContent.trim();

                if (!isNaN(valA) && !isNaN(valB)) {
                    return parseFloat(valA) - parseFloat(valB);
                }
                return valA.localeCompare(valB);
            });

            righe.forEach(riga => tabella.querySelector('tbody').appendChild(riga));
        }
    </script>

</body>

</html>


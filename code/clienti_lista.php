<?php
require_once 'config.php';
checkLogin();

$stmt = $pdo->query("SELECT c.*, u.username FROM cliente c 
                     JOIN utente u ON c.id_utente = u.id_utente 
                     ORDER BY c.cognome, c.nome");
$clienti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lista Clienti</title>
</head>
<body>
    <h1>Lista Clienti</h1>
    <a href="dashboard.php">Torna alla Dashboard</a> | 
    <a href="cliente_inserisci.php">Nuovo Cliente</a>
    
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Cognome</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Username</th>
            <th>Azioni</th>
        </tr>
        <?php foreach($clienti as $c): ?>
        <tr>
            <td><?= $c['id_cliente'] ?></td>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= htmlspecialchars($c['cognome']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['telefono']) ?></td>
            <td><?= htmlspecialchars($c['username']) ?></td>
            <td>
                <a href="cliente_modifica.php?id=<?= $c['id_cliente'] ?>">Modifica</a> |
                <a href="cliente_elimina.php?id=<?= $c['id_cliente'] ?>" 
                   onclick="return confirm('Sicuro di eliminare?')">Elimina</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
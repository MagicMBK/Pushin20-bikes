<?php
require_once 'config.php';
checkLogin();

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT id_utente FROM cliente WHERE id_cliente = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM cliente WHERE id_cliente = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM utente WHERE id_utente = ?");
        $stmt->execute([$cliente['id_utente']]);
        
        $pdo->commit();
    }
} catch(PDOException $e) {
    $pdo->rollBack();
    die('Errore eliminazione: ' . $e->getMessage());
}

header('Location: clienti_lista.php');
exit;
?>
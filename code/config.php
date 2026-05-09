<?php
session_start();

$host = 'localhost';
$db   = 'pushin20_bikes';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}

function isLogged() {
    return isset($_SESSION['id_utente']);
}

function checkLogin() {
    if (!isLogged()) {
        header('Location: login.php');
        exit;
    }
}
?>
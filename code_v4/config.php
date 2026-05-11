<?php
session_start();

$host = 'localhost';
$db = 'pushin20_bikes';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}

function isLogged(): bool
{
    return isset($_SESSION['id_utente']);
}

function checkLogin(): void
{
    if (!isLogged()) {
        header('Location: login.php');
        exit;
    }
}

function checkAdmin(): void
{
    checkLogin();
    if ($_SESSION['ruolo'] !== 'admin') {
        header('Location: dashboard.php');
        exit;
    }
}

function checkRole(array $ruoli): void
{
    checkLogin();
    if (!in_array($_SESSION['ruolo'], $ruoli)) {
        header('Location: dashboard.php');
        exit;
    }
}

function isAdmin(): bool
{
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';
}



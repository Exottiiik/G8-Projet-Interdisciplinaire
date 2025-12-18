<?php
date_default_timezone_set('Europe/Brussels');

$hote = 'localhost';
$nomBD = 'dbProjet';
$user = 'root';
$mdp = '';

try {
    $pdo = new PDO('mysql:host=' . $hote . ';dbname=' . $nomBD, $user, $mdp);
    
    $pdo->exec("SET NAMES 'utf8'");

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die('Erreur de connexion à la BD : ' . $e->getMessage());
}
?>
<?php
date_default_timezone_set('Europe/Brussels');

$hote = 'localhost';
$nomBD = 'db_sainte_isabelle';
$user = 'root';
$mdp = '';

try {
    $bd = new PDO('mysql:host=' . $hote . ';dbname=' . $nomBD, $user, $mdp);
    $bd->exec("SET NAMES 'utf8'");
} catch (Exception $e) {
    die('Erreur de connexion à la BD : ' . $e->getMessage());
}
?>
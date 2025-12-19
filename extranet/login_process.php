<?php
session_start();

require '_db.php';

if (!isset($_POST['username'], $_POST['password'], $_POST['redirect'])) {
    die('Requête invalide');
}

$username = $_POST['username'];
$password = $_POST['password'];
$redirect = $_POST['redirect'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];

    header('Location: index.php');
    exit;
}

header('Location: login'.'.php?error=1');
exit;

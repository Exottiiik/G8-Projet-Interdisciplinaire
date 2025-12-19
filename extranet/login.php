<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login Extranet</title>
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>

<h2>Connexion Extranet</h2>

<?php if (isset($_GET['error'])): ?>
<p class="error">Identifiants incorrects</p>
<?php endif; ?>

<form method="post" action="login_process.php">
    <input type="hidden" name="redirect" value="extranet">

    <label>Utilisateur</label>
    <input type="text" name="username" required>

    <label>Mot de passe</label>
    <input type="password" name="password" required>

    <button type="submit">Connexion</button>
</form>

</body>
</html>
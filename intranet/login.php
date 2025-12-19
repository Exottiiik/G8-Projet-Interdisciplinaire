<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login Intranet</title>
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>

<h2>Connexion Intranet</h2>

<?php if (isset($_GET['error'])): ?>
<p style="color:red;">Identifiants incorrects</p>
<?php endif; ?>

<form method="post" action="login_process.php">
    <input type="hidden" name="redirect" value="intranet">

    <label>Utilisateur</label><br>
    <input type="text" name="username" required><br><br>

    <label>Mot de passe</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Connexion</button>
</form>

</body>
</html>
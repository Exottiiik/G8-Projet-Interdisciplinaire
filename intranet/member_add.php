<?php
$pageTitle = "Ajouter un membre";
$themeClass = "theme-intranet";
require_once '_db.php';

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $id_service = intval($_POST['id_service']);
    $id_equipe = intval($_POST['id_equipe']);

    if (!empty($nom) && !empty($prenom) && !empty($id_service) && !empty($id_equipe)) {
        try {
            $sql = "INSERT INTO MEMBRES_INTERNES (nom, prenom, ID_service, ID_equipe) 
                    VALUES (:nom, :prenom, :serv, :eq)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':serv' => $id_service, ':eq' => $id_equipe]);
            
            header("Location: services.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

$services = $pdo->query("SELECT * FROM SERVICES")->fetchAll();
$equipes = $pdo->query("SELECT E.ID_equipe, S.nom as service_nom 
                        FROM EQUIPES E 
                        JOIN SERVICES S ON E.ID_service = S.ID_service
                        ORDER BY S.nom")->fetchAll();

include 'layout/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Nouveau Membre du Personnel</h5>
            </div>
            <div class="card-body">
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service de rattachement</label>
                        <select name="id_service" class="form-select" required>
                            <?php foreach($services as $s): ?>
                                <option value="<?= $s['ID_service'] ?>"><?= htmlspecialchars($s['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Affectation Équipe</label>
                        <select name="id_equipe" class="form-select" required>
                            <?php foreach($equipes as $eq): ?>
                                <option value="<?= $eq['ID_equipe'] ?>">
                                    Équipe N°<?= $eq['ID_equipe'] ?> (<?= htmlspecialchars($eq['service_nom']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="services.php" class="btn btn-light">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
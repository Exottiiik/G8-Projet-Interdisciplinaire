<?php
$pageTitle = "Modifier un membre";
$themeClass = "theme-intranet";
require_once '_db.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: services.php");
    exit();
}
$id_membre = intval($_GET['id']);
$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $id_service = intval($_POST['id_service']);
    $id_equipe = intval($_POST['id_equipe']);

    if (!empty($nom) && !empty($prenom)) {
        $sql = "UPDATE MEMBRES_INTERNES 
                SET nom = :nom, prenom = :prenom, ID_service = :serv, ID_equipe = :eq 
                WHERE ID_membre_interne = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom, 
            ':prenom' => $prenom, 
            ':serv' => $id_service, 
            ':eq' => $id_equipe,
            ':id' => $id_membre
        ]);
        
        header("Location: services.php");
        exit();
    }
}

$stmt = $pdo->prepare("SELECT * FROM MEMBRES_INTERNES WHERE ID_membre_interne = ?");
$stmt->execute([$id_membre]);
$membre = $stmt->fetch();

if (!$membre) { die("Membre introuvable."); }

$services = $pdo->query("SELECT * FROM SERVICES")->fetchAll();
$equipes = $pdo->query("SELECT E.ID_equipe, S.nom as service_nom FROM EQUIPES E JOIN SERVICES S ON E.ID_service = S.ID_service ORDER BY S.nom")->fetchAll();

include 'layout/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Modifier <?= htmlspecialchars($membre['prenom']) ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($membre['nom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($membre['prenom']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <select name="id_service" class="form-select">
                            <?php foreach($services as $s): ?>
                                <option value="<?= $s['ID_service'] ?>" <?= ($s['ID_service'] == $membre['ID_service']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Équipe</label>
                        <select name="id_equipe" class="form-select">
                            <?php foreach($equipes as $eq): ?>
                                <option value="<?= $eq['ID_equipe'] ?>" <?= ($eq['ID_equipe'] == $membre['ID_equipe']) ? 'selected' : '' ?>>
                                    Équipe N°<?= $eq['ID_equipe'] ?> (<?= htmlspecialchars($eq['service_nom']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="services.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-warning">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
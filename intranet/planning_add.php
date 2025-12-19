<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'layout/header.php';

$pageTitle = "Ajouter au Planning";
$themeClass = "theme-intranet";
require_once '_db.php';

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $contenu = htmlspecialchars(trim($_POST['contenu']));
    $date_heure = $_POST['date_heure'];
    $id_membre = intval($_POST['id_membre']);
    $acces = intval($_POST['acces']);

    if (!empty($contenu) && !empty($date_heure) && !empty($id_membre)) {
        try {
            $pdo->beginTransaction();

            $sqlInfo = "INSERT INTO INFORMATIONS (contenu, acces) VALUES (:contenu, :acces)";
            $stmtInfo = $pdo->prepare($sqlInfo);
            $stmtInfo->execute([':contenu' => $contenu, ':acces' => $acces]);
            
            $lastIdInfo = $pdo->lastInsertId();

            $sqlPlan = "INSERT INTO PLANNING (timestamp, ID_information, ID_membre_interne) 
                        VALUES (:ts, :id_info, :id_membre)";
            $stmtPlan = $pdo->prepare($sqlPlan);
            $stmtPlan->execute([
                ':ts' => $date_heure,
                ':id_info' => $lastIdInfo,
                ':id_membre' => $id_membre
            ]);

            $pdo->commit();

            header("Location: planning.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur technique : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}


$membres = $pdo->query("SELECT ID_membre_interne, nom, prenom FROM MEMBRES_INTERNES ORDER BY nom ASC")->fetchAll();

?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h4 class="mb-0 text-primary"><i class="bi bi-calendar-plus"></i> Nouvel événement</h4>
            </div>
            <div class="card-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description de l'événement</label>
                        <input type="text" name="contenu" class="form-control" placeholder="Ex: Garde Urgences Nuit" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date et Heure</label>
                            <input type="datetime-local" name="date_heure" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Visibilité</label>
                            <select name="acces" class="form-select">
                                <option value="1" selected>🔒 Interne (Intranet seulement)</option>
                                <option value="0">🌍 Publique (Visible sur Extranet)</option>
                            </select>
                            <div class="form-text">Définit si les partenaires externes voient ceci.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Responsable</label>
                        <select name="id_membre" class="form-select" required>
                            <option value="" disabled selected>Choisir un membre...</option>
                            <?php foreach ($membres as $m): ?>
                                <option value="<?= $m['ID_membre_interne'] ?>">
                                    <?= htmlspecialchars($m['nom'] . ' ' . $m['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="planning.php" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<?php
$pageTitle = "Prise de Rendez-vous";
$themeClass = "theme-extranet";

require_once '_db.php';

$message = "";

// Traitement du formulaire
if (isset($_POST['choix_rdv'])) {
    // Validation des champs
    if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['info']) || empty($_POST['rdv'])) {
        $message = "<div class='alert alert-warning mt-3'>Tous les champs sont obligatoires.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            // Récupérer un médecin par défaut
            $medecin = $pdo->query("SELECT ID_membre_interne FROM MEMBRES_INTERNES LIMIT 1")->fetch();
            if (!$medecin) {
                throw new Exception("Aucun médecin interne trouvé.");
            }

            // Vérifier ou créer le patient
            $reqPatient = $pdo->prepare("SELECT ID_membre_externe FROM MEMBRES_EXTERNES WHERE nom = ?  AND prenom = ?");
            $reqPatient->execute([$_POST['nom'], $_POST['prenom']]);
            $patient = $reqPatient->fetch();

            if (!$patient) {
                $pdo->prepare("INSERT INTO MEMBRES_EXTERNES (nom, prenom) VALUES (?, ? )")
                    ->execute([$_POST['nom'], $_POST['prenom']]);
                $id_patient = $pdo->lastInsertId();
            } else {
                $id_patient = $patient['ID_membre_externe'];
            }

            // Créer le motif du RDV
            $pdo->prepare("INSERT INTO INFORMATIONS (contenu, acces) VALUES (?, 0)")
                ->execute([$_POST['info']]);
            $id_info = $pdo->lastInsertId();

            // Créer le créneau dans le planning
            $pdo->prepare("INSERT INTO PLANNING (timestamp, ID_information, ID_membre_interne) VALUES (?, ?, ?)")
                ->execute([$_POST['rdv'], $id_info, $medecin['ID_membre_interne']]);
            $id_planning = $pdo->lastInsertId();

            // Lier le patient au RDV
            $pdo->prepare("INSERT INTO PRENDRE_RDV (ID_membre_externe, ID_planning) VALUES (?, ?)")
                ->execute([$id_patient, $id_planning]);

            $pdo->commit();
            $message = "<div class='alert alert-success mt-3'>RDV confirmé pour " . htmlspecialchars($_POST['nom']) . " !</div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger mt-3'>Erreur :  " . $e->getMessage() . "</div>";
        }
    }
}

include 'layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $pageTitle ?></h1>
    <a href="#" class="btn btn-theme"><i class="bi bi-plus-circle"></i> Action</a>
</div>

<?php
// generation des dates 
$dates = [];
$start = new DateTime('2025-12-18 09:00:00');
$end   = new DateTime('2025-12-31 17:00:00');
$interval = new DateInterval('PT1H'); // intervalle de 1heure
while ($start <= $end) {
    if($start->format('H') >= 9 && $start->format('H') <= 17) $dates[] = $start->format('Y-m-d H:i:s');
    $start->add($interval); // ca genere des creneaux horaires de 9h jusque 17h
}
?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <p>Veuillez remplir vos informations pour prendre rendez-vous.</p>
        
        <?= $message ?>
        
        <form method="post" action="">
            <fieldset class="p-3 border rounded">
                <legend class="float-none w-auto px-2 text-primary">Formulaire Extranet</legend>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Nom :</label>
                        <input type="text" name="nom" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Prénom :</label>
                        <input type="text" name="prenom" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Raison du rendez-vous :</label>
                    <textarea name="info" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label>Date souhaitée :</label>
                    <select name="rdv" class="form-select">
                        <?php foreach ($dates as $date): ?>
                        <option value="<?= $date ?>"><?= $date ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="choix_rdv" class="btn btn-success">Envoyer la demande</button>
            </fieldset>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
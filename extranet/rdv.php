<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Prise de Rendez-vous";
$themeClass = "theme-extranet"; // Active le vert

require_once '_db.php';

$message = "";

// 1. Récupération des médecins pour la liste déroulante (C'est mieux que le hasard)
$medecins = $pdo->query("SELECT ID_membre_interne, nom, prenom FROM MEMBRES_INTERNES WHERE ID_service = 1 ORDER BY nom")->fetchAll();

// 2. Génération des dates (Ton code existant)
$dates = [];
$start = new DateTime('2025-12-18 09:00:00'); // Tu pourras changer ça par new DateTime('now') + modifs plus tard
$end   = new DateTime('2025-12-31 17:00:00');
$interval = new DateInterval('PT1H'); 
while ($start <= $end) {
    if($start->format('H') >= 9 && $start->format('H') <= 17) $dates[] = $start->format('Y-m-d H:i:s');
    $start->add($interval);
}

// 3. Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['choix_rdv'])) {
    
    // Nettoyage basique
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $info = htmlspecialchars(trim($_POST['info']));
    $date_rdv = $_POST['rdv'];
    $id_medecin = intval($_POST['id_medecin']); // On récupère le médecin choisi

    if (empty($nom) || empty($prenom) || empty($info) || empty($date_rdv) || empty($id_medecin)) {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            // A. GESTION PATIENT (Vérifier si existe, sinon créer)
            // Note: On vérifie sur Nom+Prénom. Dans la vraie vie, on utiliserait l'email ou le registre national.
            $reqPatient = $pdo->prepare("SELECT ID_membre_externe FROM MEMBRES_EXTERNES WHERE nom = ? AND prenom = ?");
            $reqPatient->execute([$nom, $prenom]);
            $patient = $reqPatient->fetch();

            if (!$patient) {
                $pdo->prepare("INSERT INTO MEMBRES_EXTERNES (nom, prenom) VALUES (?, ?)")->execute([$nom, $prenom]);
                $id_patient = $pdo->lastInsertId();
            } else {
                $id_patient = $patient['ID_membre_externe'];
            }

            // B. GESTION INFO (Le motif)
            // Acces = 0 car c'est créé depuis l'extranet
            $pdo->prepare("INSERT INTO INFORMATIONS (contenu, acces) VALUES (?, 0)")->execute([$info]);
            $id_info = $pdo->lastInsertId();

            // C. GESTION PLANNING (Création du slot horaire pour le médecin choisi)
            $pdo->prepare("INSERT INTO PLANNING (timestamp, ID_information, ID_membre_interne) VALUES (?, ?, ?)")
                ->execute([$date_rdv, $id_info, $id_medecin]);
            $id_planning = $pdo->lastInsertId();

            // D. GESTION LIEN (Lier le patient au créneau)
            $pdo->prepare("INSERT INTO PRENDRE_RDV (ID_membre_externe, ID_planning) VALUES (?, ?)")
                ->execute([$id_patient, $id_planning]);

            $pdo->commit();
            $message = "<div class='alert alert-success'>✅ Rendez-vous confirmé pour M/Mme " . strtoupper($nom) . " !</div>";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Erreur technique : " . $e->getMessage() . "</div>";
        }
    }
}

include 'layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-success">Prise de Rendez-vous</h1>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Formulaire de demande</h5>
            </div>
            
            <div class="card-body">
                <?= $message ?>
                <p class="text-muted mb-4">Veuillez remplir vos informations pour solliciter un médecin.</p>
                
                <form method="post" action="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Votre Nom</label>
                            <input type="text" name="nom" class="form-control" required placeholder="Ex: Dupont">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Votre Prénom</label>
                            <input type="text" name="prenom" class="form-control" required placeholder="Ex: Jean">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Médecin souhaité</label>
                        <select name="id_medecin" class="form-select" required>
                            <option value="" disabled selected>-- Sélectionnez un docteur --</option>
                            <?php foreach ($medecins as $doc): ?>
                            <option value="<?= $doc['ID_membre_interne'] ?>">
                                Dr. <?= htmlspecialchars($doc['nom'] . ' ' . $doc['prenom']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Motif de la consultation</label>
                        <textarea name="info" class="form-control" rows="2" required placeholder="Ex: Douleurs abdominales..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Créneau souhaité</label>
                        <select name="rdv" class="form-select">
                            <?php foreach ($dates as $date): ?>
                            <option value="<?= $date ?>">
                                <?= date('d/m/Y à H:i', strtotime($date)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Les créneaux sont affichés par tranches d'une heure.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="choix_rdv" class="btn btn-success btn-lg">
                            Confirmer le rendez-vous
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'layout/header.php';

$pageTitle = "Planning des gardes";
$themeClass = "theme-intranet";
require_once '_db.php';

// MODIFICATION SQL : On ajoute les JOINS vers PRENDRE_RDV et MEMBRES_EXTERNES
// Cela permet de récupérer le nom du patient SI c'est un rendez-vous externe.
$sql = "SELECT 
            P.timestamp, 
            I.contenu, 
            I.acces, 
            M.nom AS nom_medecin, M.prenom AS prenom_medecin,
            ME.nom AS nom_patient, ME.prenom AS prenom_patient
        FROM PLANNING P
        JOIN INFORMATIONS I ON P.ID_information = I.ID_informations
        JOIN MEMBRES_INTERNES M ON P.ID_membre_interne = M.ID_membre_interne
        -- On tente de lier un patient externe, mais si y'en a pas (NULL), ça marche quand même (LEFT JOIN)
        LEFT JOIN PRENDRE_RDV PR ON P.ID_planning = PR.ID_planning
        LEFT JOIN MEMBRES_EXTERNES ME ON PR.ID_membre_externe = ME.ID_membre_externe
        ORDER BY P.timestamp ASC";

$planning = $pdo->query($sql)->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>📅 Planning opérationnel</h1>
    <a href="planning_add.php" class="btn btn-theme">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-2" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
        Ajouter un événement
    </a>
</div>

<div class="row">
    <?php foreach($planning as $event): ?>
        
    <?php 
        $isRDV = !empty($event['nom_patient']); 
        $cardBorder = $isRDV ? 'border-success' : 'border-primary';
        $badgeClass = $event['acces'] == 1 ? 'bg-primary' : 'bg-success';
        $typeLabel  = $event['acces'] == 1 ? 'Interne' : 'Extranet';
    ?>

    <div class="col-md-6 mb-3">
        <div class="card h-100 shadow-sm border-0 border-start border-4 <?= $cardBorder ?>">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom-0 pb-0 pt-3">
                <span class="badge <?= $badgeClass ?>"><?= $typeLabel ?></span>
                <small class="text-muted fw-bold">
                    <?= date('d/m/Y à H:i', strtotime($event['timestamp'])) ?>
                </small>
            </div>

            <div class="card-body">
                <h5 class="card-title mb-3"><?= htmlspecialchars($event['contenu']) ?></h5>
                
                <?php if($isRDV): ?>
                    <div class="alert alert-light border mb-0">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-success me-2">🏥 <strong>Patient :</strong></span>
                            <span><?= htmlspecialchars(strtoupper($event['nom_patient']) . ' ' . $event['prenom_patient']) ?></span>
                        </div>
                        <div class="d-flex align-items-center text-muted small">
                            <span class="me-2">👨‍⚕️ Avec Dr. :</span>
                            <span><?= htmlspecialchars($event['nom_medecin']) ?></span>
                        </div>
                    </div>

                <?php else: ?>
                    <p class="card-text text-secondary">
                        Responsable : <strong>Dr. <?= htmlspecialchars($event['nom_medecin'] . ' ' . $event['prenom_medecin']) ?></strong>
                    </p>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'layout/footer.php'; ?>

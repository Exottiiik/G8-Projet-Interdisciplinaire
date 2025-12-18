<?php
$pageTitle = "Planning des gardes";
$themeClass = "theme-intranet";
require_once '_db.php';

$sql = "SELECT P.timestamp, I.contenu, M.nom, M.prenom 
        FROM PLANNING P
        JOIN INFORMATIONS I ON P.ID_information = I.ID_informations
        JOIN MEMBRES_INTERNES M ON P.ID_membre_interne = M.ID_membre_interne
        ORDER BY P.timestamp ASC";

$planning = $pdo->query($sql)->fetchAll();

include 'layout/header.php'; 
?>

<h1 class="mb-4">📅 Planning opérationnel</h1>

<div class="row">
    <?php foreach($planning as $event): ?>
    <div class="col-md-6 mb-3">
        <div class="card h-100 border-start border-4 border-primary shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-2">
                    <?= date('d/m/Y à H:i', strtotime($event['timestamp'])) ?>
                </div>
                <h5 class="card-title"><?= htmlspecialchars($event['contenu']) ?></h5>
                <p class="card-text text-secondary">
                    Responsable : <strong>Dr. <?= htmlspecialchars($event['nom']) ?></strong>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'layout/footer.php'; ?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include 'layout/header.php';

$pageTitle = "Gestion des Équipes";
$themeClass = "theme-intranet";
require_once '_db.php';

$sql = "SELECT S.nom AS nom_service, M.ID_membre_interne, M.ID_equipe, M.nom, M.prenom 
        FROM MEMBRES_INTERNES M 
        JOIN SERVICES S ON M.ID_service = S.ID_service 
        ORDER BY S.nom ASC, M.ID_equipe ASC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();


$organisation = [];
foreach ($rows as $row) {
    $nomService = $row['nom_service'];
    $idEquipe = $row['ID_equipe'];
    
    if (!isset($organisation[$nomService])) { $organisation[$nomService] = []; }
    if (!isset($organisation[$nomService][$idEquipe])) { $organisation[$nomService][$idEquipe] = []; }
    
    $organisation[$nomService][$idEquipe][] = $row;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🏥 Services & Équipes</h1>
    <a href="member_add.php" class="btn btn-theme">
        <i class="bi bi-person-plus-fill"></i> Ajouter un membre
    </a>
</div>

<div class="row">
    <?php foreach($organisation as $serviceName => $equipes): ?>
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-hospital"></i> Service : <?= htmlspecialchars($serviceName) ?></h4>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <?php foreach($equipes as $idEquipe => $membres): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border-start border-4 border-info">
                            <div class="card-body">
                                <h5 class="card-title text-secondary border-bottom pb-2">
                                    Équipe N° <?= $idEquipe ?>
                                </h5>
                                <ul class="list-unstyled mt-3">
                                    <?php foreach($membres as $m): ?>
                                    <li class="mb-2 d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div class="d-flex align-items-center">
                                            <strong><?= htmlspecialchars(strtoupper($m['nom']) . ' ' . $m['prenom']) ?></strong>
                                        </div>

                                        <a href="member_edit.php?id=<?= $m['ID_membre_interne'] ?>" 
                                        class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" 
                                        title="Modifier ce membre">
                                            <span class="small">Modifier</span>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'layout/footer.php'; ?>

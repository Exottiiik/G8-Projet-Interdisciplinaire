<?php
$pageTitle = "Services & Équipes";
$themeClass = "theme-intranet";
require_once '_db.php';

$sql = "SELECT S.nom AS nom_service, M.nom, M.prenom 
        FROM SERVICES S 
        JOIN MEMBRES_INTERNES M ON S.ID_service = M.ID_service 
        ORDER BY S.nom";
$stmt = $pdo->query($sql);
$equipes = $stmt->fetchAll();

include 'layout/header.php'; 
?>

<h1 class="mb-4">🏥 Services Hospitaliers</h1>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Membre du personnel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($equipes as $e): ?>
                        <tr>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($e['nom_service']) ?></span></td>
                            <td><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
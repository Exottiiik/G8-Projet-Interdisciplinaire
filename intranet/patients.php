<?php
$pageTitle = "Gestion des Patients";
$themeClass = "theme-intranet";
require_once '_db.php';

// REQUETE SQL
$sql = "SELECT * FROM PATIENTS ORDER BY nom ASC";
$stmt = $pdo->query($sql);
$patients = $stmt->fetchAll();

include 'layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>👥 Liste des Patients</h1>
    <span class="badge bg-primary rounded-pill"><?= count($patients) ?> patients enregistrés</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registre National</th>
                        <th>Nom Prénom</th>
                        <th>Sexe</th>
                        <th>Date Naissance</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($patients as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['registre_national']) ?></td>
                        <td class="fw-bold">
                            <?= htmlspecialchars(strtoupper($p['nom']) . ' ' . $p['prenom']) ?>
                        </td>
                        <td>
                            <?php if($p['sexe'] === 'M'): ?>
                                <span class="badge bg-info text-dark">Homme</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Femme</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($p['date_naissance'])) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($p['email']) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-envelope"></i> Email
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
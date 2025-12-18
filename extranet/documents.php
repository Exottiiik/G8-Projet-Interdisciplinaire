<?php
$pageTitle = "Documents extranet";
$themeClass = "theme-extranet"; 
require_once '_db.php';

$sql = "SELECT * FROM DOCUMENTS";
$docs = $pdo->query($sql)->fetchAll();

include 'layout/header.php'; 
?>

<h1 class="mb-4 text-success">📄 Documents Partagés</h1>

<div class="list-group shadow-sm">
    <?php foreach($docs as $d): ?>
    <a href="<?= htmlspecialchars($d['lien']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
        <div>
            <h5 class="mb-1"><?= htmlspecialchars($d['nom']) ?></h5>
            <small class="text-muted">Type : <?= htmlspecialchars(strtoupper($d['type'])) ?></small>
        </div>
        <span class="badge bg-success rounded-pill">Télécharger</span>
    </a>
    <?php endforeach; ?>
</div>

<?php include 'layout/footer.php'; ?>
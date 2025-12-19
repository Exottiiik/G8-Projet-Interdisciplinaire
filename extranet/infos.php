<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Informations Partagées";
$themeClass = "theme-extranet";

require_once '_db.php';

$sql = "SELECT I.ID_informations, I.contenu, D.nom AS doc_nom, D.lien AS doc_lien, D.type AS doc_type
        FROM INFORMATIONS I
        LEFT JOIN EST_CONTENU EC ON I.ID_informations = EC.ID_information
        LEFT JOIN DOCUMENTS D ON EC.ID_document = D.ID_document
        WHERE I.acces = 1
        ORDER BY I.ID_informations ASC";

$infos = $pdo->query($sql)->fetchAll();

include 'layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-success">
        Informations Hôpital
    </h1>
</div>

<div class="row">
    <?php if(empty($infos)): ?>
        <div class="col-12">
            <div class="alert alert-info">Aucune information partagée pour le moment.</div>
        </div>
    <?php endif; ?>

    <?php foreach($infos as $info): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm border-0 border-top border-4 border-success">
            <div class="card-body">
                <h5 class="card-title text-success mb-3">Information</h5>
                
                <p class="card-text fs-5">
                    <?= htmlspecialchars($info['contenu']) ?>
                </p>

                <?php if(!empty($info['doc_lien'])): ?>
                    <hr>
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <small class="text-muted">
                            Fichier joint : <strong><?= htmlspecialchars($info['doc_nom']) ?></strong>
                        </small>
                        
                        <a href="<?= htmlspecialchars($info['doc_lien']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
                            Télécharger (<?= strtoupper($info['doc_type']) ?>)
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'layout/footer.php'; ?>

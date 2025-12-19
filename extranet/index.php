<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Extranet - Partenaires";
include 'layout/header.php';
?>

<div class="text-center mb-5">
    <h1 class="display-4 fw-bold text-success">Espace Partenaires</h1>
    <p class="lead text-secondary">Accès réservé aux membres externes.</p>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-success mb-3">ℹ️ Infos</h3>
                <p class="card-text text-muted">Consulter les informations partagées.</p>
                <a href="infos.php" class="btn btn-outline-success w-100 mt-2">Voir</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-success mb-3">📆 Rendez-vous</h3>
                <p class="card-text text-muted">Prendre un nouveau rendez-vous.</p>
                <a href="rdv.php" class="btn btn-outline-success w-100 mt-2">Réserver</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-success mb-3">📄 Documents</h3>
                <p class="card-text text-muted">Télécharger les formulaires.</p>
                <a href="documents.php" class="btn btn-outline-success w-100 mt-2">Accéder</a>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>

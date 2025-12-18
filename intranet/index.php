<?php
$pageTitle = "Intranet - Tableau de bord";
include 'layout/header.php';
?>

<div class="text-center mb-5">
    <h1 class="display-4 fw-bold text-primary">Intranet Personnel</h1>
    <p class="lead text-secondary">Bienvenue sur l'espace de gestion interne.</p>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-primary mb-3">👥 Patients</h3>
                <p class="card-text text-muted">Gestion des admissions et dossiers.</p>
                <a href="patients.php" class="btn btn-outline-primary w-100 mt-2">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-primary mb-3">🏥 Services</h3>
                <p class="card-text text-muted">Gestion des équipes médicales.</p>
                <a href="services.php" class="btn btn-outline-primary w-100 mt-2">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-primary mb-3">📅 Planning</h3>
                <p class="card-text text-muted">Horaires et disponibilités.</p>
                <a href="planning.php" class="btn btn-outline-primary w-100 mt-2">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 shadow-sm card-hover border-0">
            <div class="card-body text-center">
                <h3 class="card-title text-primary mb-3">📂 Documents</h3>
                <p class="card-text text-muted">Procédures et archives.</p>
                <a href="documents.php" class="btn btn-outline-primary w-100 mt-2">Accéder</a>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
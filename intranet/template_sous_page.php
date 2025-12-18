<?php
$pageTitle = "Titre de la page";

// 2. LOGIQUE PHP (BACKEND)
// Ici tu mettras tes requêtes SQL plus tard
require_once '_db.php';

include 'layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= $pageTitle ?></h1>
    
    <a href="#" class="btn btn-theme">
        <i class="bi bi-plus-circle"></i> Action
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <p>Contenu de la page...</p>
        
        <?php
        // Exemple d'endroit où afficher tes données PHP
        // foreach($patients as $patient) { ... }
        ?>
        
        <div class="alert alert-info">
            Zone prête pour l'injection PHP.
        </div>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
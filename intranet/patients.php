<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '_db.php';



$action = $_POST['action'] ?? '';
$message = '';
$patients = [];


// AJOUTER UN PATIENT
if ($action === 'ajouter_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $registre_national = $_POST['registre_national'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $sexe = $_POST['sexe'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $date_naissance = $_POST['date_naissance'] ?? '';
        $id_membre_interne = $_POST['id_membre_interne'] ?? 1;


        if (empty($registre_national) || empty($nom) || empty($prenom) || empty($sexe) || empty($email) || empty($telephone) || empty($date_naissance)) {
            $message = '<div class="alert alert-error">Tous les champs sont obligatoires !</div>';
        } else {
            $sql = "INSERT INTO PATIENTS (registre_national, nom, prenom, sexe, email, telephone, date_naissance, ID_membre_interne) 
                    VALUES (:registre_national, :nom, :prenom, :sexe, :email, :telephone, :date_naissance, :id_membre_interne)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':registre_national' => $registre_national,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':sexe' => $sexe,
                ':email' => $email,
                ':telephone' => $telephone,
                ':date_naissance' => $date_naissance,
                ':id_membre_interne' => $id_membre_interne
            ]);
            $message = '<div class="alert alert-success">Patient ajouté avec succès !</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}


// MODIFIER UN PATIENT        
if ($action === 'modifier_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $registre_national = $_POST['registre_national'] ?? '';
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $id_membre_interne = $_POST['id_membre_interne'] ?? '';

        // Fonction pour nettoyer et valider les champs
        function cleanAndValidateField($field) {
            // Supprimer les espaces en début/fin
            $field = trim($field);
            
            // Vérifier si le champ n'est que des espaces ou caractères spéciaux
            if (empty($field) || !preg_match('/[a-zA-Z0-9àâäéèêëïîôöùûüçœæ@\.\-\+\(\)]/i', $field)) {
                return null; // Retourne null si le champ est invalide
            }
            
            return $field;
        }

        // Valider le registre national (obligatoire)
        if (empty($registre_national)) {
            $message = '<div class="alert alert-error">Le numéro de registre national est obligatoire !</div>';
        } else {
            // Nettoyer les champs optionnels
            $nom_clean = cleanAndValidateField($nom);
            $prenom_clean = cleanAndValidateField($prenom);
            $email_clean = cleanAndValidateField($email);
            $telephone_clean = cleanAndValidateField($telephone);

            // Valider l'email si fourni
            if ($email_clean !== null && !filter_var($email_clean, FILTER_VALIDATE_EMAIL)) {
                $message = '<div class="alert alert-error">L\'email fourni n\'est pas valide !</div>';
            } else {
                // Vérifier qu'au moins un champ a été fourni pour modification
                if ($nom_clean === null && $prenom_clean === null && $email_clean === null && $telephone_clean === null && empty($id_membre_interne)) {
                    $message = '<div class="alert alert-error">Vous devez fournir au moins un champ à modifier !</div>';
                } else {
                    // Construire dynamiquement la requête UPDATE
                    $fields_to_update = [];
                    $params = [':registre_national' => $registre_national];

                    if ($nom_clean !== null) {
                        $fields_to_update[] = "nom = :nom";
                        $params[':nom'] = $nom_clean;
                    }

                    if ($prenom_clean !== null) {
                        $fields_to_update[] = "prenom = :prenom";
                        $params[':prenom'] = $prenom_clean;
                    }

                    if ($email_clean !== null) {
                        $fields_to_update[] = "email = :email";
                        $params[':email'] = $email_clean;
                    }

                    if ($telephone_clean !== null) {
                        $fields_to_update[] = "telephone = :telephone";
                        $params[':telephone'] = $telephone_clean;
                    }

                    if (!empty($id_membre_interne)) {
                        $fields_to_update[] = "ID_membre_interne = :id_membre_interne";
                        $params[':id_membre_interne'] = $id_membre_interne;
                    }

                    // Construire la requête SQL dynamiquement
                    $sql = "UPDATE PATIENTS SET " . implode(", ", $fields_to_update) . " WHERE registre_national = :registre_national";
                    
                    $stmt = $pdo->prepare($sql);
                    $result = $stmt->execute($params);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        $message = '<div class="alert alert-success">Patient modifié avec succès !</div>';
                    } else {
                        $message = '<div class="alert alert-error">Aucun patient trouvé avec ce numéro de registre ou aucune modification détectée !</div>';
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}


// SUPPRIMER UN PATIENT
if ($action === 'supprimer_patient' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $registre_national = $_POST['registre_national'] ?? '';


        if (empty($registre_national)) {
            $message = '<div class="alert alert-error">Veuillez entrer le numéro de registre !</div>';
        } else {
            $sql = "DELETE FROM PATIENTS WHERE registre_national = :registre_national";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([':registre_national' => $registre_national]);
            
            if ($result && $stmt->rowCount() > 0) {
                $message = '<div class="alert alert-success">Patient supprimé avec succès !</div>';
            } else {
                $message = '<div class="alert alert-error">Aucun patient trouvé avec ce numéro de registre !</div>';
            }
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-error">Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}


// RÉCUPÉRER TOUS LES PATIENTS
try {
    $sql = "SELECT p.registre_national, p.nom, p.prenom, p.sexe, p.date_naissance, p.email, p.telephone, p.ID_membre_interne, 
                   m.nom AS medecin_nom, m.prenom AS medecin_prenom 
            FROM PATIENTS p 
            LEFT JOIN MEMBRES_INTERNES m ON p.ID_membre_interne = m.ID_membre_interne";
    $stmt = $pdo->query($sql);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '<div class="alert alert-error">Erreur lors du chargement des patients : ' . htmlspecialchars($e->getMessage()) . '</div>';
}


$afficher_formulaire_ajouter = isset($_POST['btn_ajouter']);
$afficher_formulaire_modifier = isset($_POST['btn_modifier']);
$afficher_formulaire_supprimer = isset($_POST['btn_supprimer']);
?>


<?php
$pageTitle = 'Gestion des Patients';


// RÉCUPÉRER TOUS LES MÉDECINS
$medecins = [];
try {
    $sql = "SELECT ID_membre_interne, nom, prenom FROM MEMBRES_INTERNES ORDER BY nom, prenom";
    $stmt = $pdo->query($sql);
    $medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '<div class="alert alert-error">Erreur lors du chargement des médecins : ' . htmlspecialchars($e->getMessage()) . '</div>';
}


include 'layout/header.php';
?>


<main class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-2"> Gestion des Patients</h1>
            <p class="text-muted">Hôpital Sainte-Isabelle - Système de Gestion</p>
        </div>
    </div>


    <?php echo $message; ?>


    <!-- BOUTONS PRINCIPAUX -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="btn-group" role="group">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="btn_ajouter" class="btn btn-primary">➕ Ajouter Patient</button>
                </form>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="btn_modifier" class="btn btn-info">✏️ Modifier Patient</button>
                </form>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="btn_supprimer" class="btn btn-danger">🗑️ Supprimer Patient</button>
                </form>
            </div>
        </div>
    </div>


    <!-- FORMULAIRE AJOUTER -->
    <div class="row mb-4 <?php echo !$afficher_formulaire_ajouter ? 'd-none' : ''; ?>">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">➕ Ajouter un Nouveau Patient</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="ajouter_patient">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_registre" class="form-label">Numéro de Registre National *</label>
                                <input type="number" class="form-control" id="add_registre" name="registre_national" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="add_nom" name="nom" required>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_prenom" class="form-label">Prénom *</label>
                                <input type="text" class="form-control" id="add_prenom" name="prenom" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_sexe" class="form-label">Sexe *</label>
                                <select class="form-select" id="add_sexe" name="sexe" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M">Masculin</option>
                                    <option value="F">Féminin</option>
                                </select>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_telephone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="add_telephone" name="telephone" placeholder="Ex: 0123456789" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_date_naissance" class="form-label">Date de Naissance *</label>
                                <input type="date" class="form-control" id="add_date_naissance" name="date_naissance" required>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_medecin" class="form-label">Médecin Responsable *</label>
                                <select class="form-select" id="add_medecin" name="id_membre_interne" required>
                                    <option value="">-- Sélectionner un médecin --</option>
                                    <?php foreach ($medecins as $med): ?>
                                        <option value="<?php echo $med['ID_membre_interne']; ?>">
                                            Dr. <?php echo htmlspecialchars($med['prenom'] . ' ' . $med['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button type="submit" class="btn btn-success">✓ Ajouter</button>
                            <button type="reset" class="btn btn-secondary">↻ Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- FORMULAIRE MODIFIER -->
    <div class="row mb-4 <?php echo !$afficher_formulaire_modifier ? 'd-none' : ''; ?>">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">✏️ Modifier un Patient</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="modifier_patient">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="mod_registre" class="form-label">Numéro de Registre National *</label>
                                <input type="number" class="form-control" id="mod_registre" name="registre_national" required>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <small>ℹ️ Les champs suivants sont optionnels. Laissez-les vides pour ne pas les modifier.</small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mod_nom" class="form-label">Nouveau Nom</label>
                                <input type="text" class="form-control" id="mod_nom" name="nom" placeholder="Nouveau nom">
                            </div>
                            <div class="col-md-6">
                                <label for="mod_prenom" class="form-label">Nouveau Prénom</label>
                                <input type="text" class="form-control" id="mod_prenom" name="prenom" placeholder="Nouveau prénom">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mod_email" class="form-label">Nouvel Email</label>
                                <input type="email" class="form-control" id="mod_email" name="email" placeholder="Nouveau email">
                            </div>
                            <div class="col-md-6">
                                <label for="mod_telephone" class="form-label">Nouveau Téléphone</label>
                                <input type="tel" class="form-control" id="mod_telephone" name="telephone" placeholder="Nouveau n° de téléphone">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mod_medecin" class="form-label">Médecin Responsable</label>
                                <select class="form-select" id="mod_medecin" name="id_membre_interne">
                                    <option value="">-- Laisser inchangé --</option>
                                    <?php foreach ($medecins as $med): ?>
                                        <option value="<?php echo $med['ID_membre_interne']; ?>">
                                            Dr. <?php echo htmlspecialchars($med['prenom'] . ' ' . $med['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>


                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button type="submit" class="btn btn-success">✓ Modifier</button>
                            <button type="reset" class="btn btn-secondary">↻ Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- FORMULAIRE SUPPRIMER -->
    <div class="row mb-4 <?php echo !$afficher_formulaire_supprimer ? 'd-none' : ''; ?>">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">🗑️ Supprimer un Patient</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="supprimer_patient">
                        
                        <div class="mb-3">
                            <label for="del_registre" class="form-label">Numéro de Registre National à Supprimer *</label>
                            <input type="number" class="form-control" id="del_registre" name="registre_national" required>
                        </div>


                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button type="submit" class="btn btn-danger" onclick="return confirm(\'⚠️ Êtes-vous sûr de vouloir supprimer ce patient ? Cette action est irréversible.\');">✓ Supprimer</button>
                            <button type="reset" class="btn btn-secondary">↻ Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- TABLE DES PATIENTS -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📋 Liste des Patients</h5>
                </div>
                <div class="card-body">
                    <?php if (count($patients) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Registre National</th>
                                        <th>Nom Complet</th>
                                        <th>Sexe</th>
                                        <th>Date de Naissance</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Médecin Responsable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $p): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['registre_national']); ?></td>
                                            <td><?php echo htmlspecialchars(strtoupper($p['nom']) . ' ' . $p['prenom']); ?></td>
                                            <td><?php echo $p['sexe'] === 'M' ? 'Homme' : 'Femme'; ?></td>
                                            <td><?php echo $p['date_naissance'] ? date('d/m/Y', strtotime($p['date_naissance'])) : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                                            <td><?php echo $p['telephone'] ? htmlspecialchars($p['telephone']) : '-'; ?></td>
                                            <td><?php echo $p['medecin_prenom'] && $p['medecin_nom'] ? htmlspecialchars('Dr. ' . $p['medecin_prenom'] . ' ' . $p['medecin_nom']) : '<span class="text-muted">-</span>'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aucun patient trouvé dans la base de données.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

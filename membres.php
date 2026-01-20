<?php
include 'config.php';
redirectIfNotLogged();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

$query_user = "SELECT * FROM utilisateurs WHERE id = :user_id";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bindParam(':user_id', $user_id);
$stmt_user->execute();
$utilisateur = $stmt_user->fetch(PDO::FETCH_ASSOC);


// Ajouter un membre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_membre'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $sexe = $_POST['sexe'];
    $date_entree = $_POST['date_entree'];
    $plan = $_POST['plan'];
    
    // Générer un code membre unique
    $code_membre = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1) . date('His'));
    
    $query = "INSERT INTO membres (code_membre, nom, prenom, telephone, email, adresse, sexe, date_entree, plan,  utilisateur_id) 
              VALUES (:code_membre, :nom, :prenom, :telephone, :email, :adresse, :sexe, :date_entree, :plan,  :utilisateur_id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':code_membre', $code_membre);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':sexe', $sexe);
    $stmt->bindParam(':date_entree', $date_entree);
    $stmt->bindParam(':plan', $plan);
    $stmt->bindParam(':utilisateur_id', $user_id);
    
    if ($stmt->execute()) {
        $success = "Membre ajouté avec succès! Code: $code_membre";
    } else {
        $error = "Erreur lors de l'ajout du membre";
    }
}

// Modifier un membre
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_membre'])) {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $sexe = $_POST['sexe'];
    $date_entree = $_POST['date_entree'];
    $plan = $_POST['plan'];
    
    $query = "UPDATE membres SET nom = :nom, prenom = :prenom, telephone = :telephone, email = :email, 
              adresse = :adresse, sexe = :sexe, date_entree = :date_entree, plan = :plan, 
              WHERE id = :id AND utilisateur_id = :utilisateur_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':telephone', $telephone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':adresse', $adresse);
    $stmt->bindParam(':sexe', $sexe);
    $stmt->bindParam(':date_entree', $date_entree);
    $stmt->bindParam(':plan', $plan);
    $stmt->bindParam(':utilisateur_id', $user_id);
    
    if ($stmt->execute()) {
        $success = "Membre modifié avec succès!";
    } else {
        $error = "Erreur lors de la modification du membre";
    }
}

// Supprimer un membre
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Vérifier que le membre appartient à l'utilisateur connecté
    $query = "DELETE FROM membres WHERE id = :id AND utilisateur_id = :utilisateur_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $delete_id);
    $stmt->bindParam(':utilisateur_id', $user_id);
    
    if ($stmt->execute()) {
        $success = "Membre supprimé avec succès!";
    } else {
        $error = "Erreur lors de la suppression du membre";
    }
    
    // Rediriger pour éviter la resoumission du formulaire
    header("Location: membres.php");
    exit();
}

// Récupérer un membre pour modification
$membre_a_modifier = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $query = "SELECT * FROM membres WHERE id = :id AND utilisateur_id = :utilisateur_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $edit_id);
    $stmt->bindParam(':utilisateur_id', $user_id);
    $stmt->execute();
    $membre_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Récupérer la liste des membres
$query = "SELECT * FROM membres WHERE utilisateur_id = :user_id ORDER BY nom ASC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$membres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO - Gestion des Membres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #041e64ff;
            --secondary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        .sidebar {
            background-color: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .user-info {
            color: white;
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--secondary);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        
        .btn-finex {
            background-color: #3498db;
            color: white;
            border: none;
        }
        .btn-actions {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="mb-0">Gestion des Membres</h2>

                    <button class="btn btn-success no-print" onclick="imprimerMembres()">
                        <i class="fas fa-print me-1"></i> Imprimer liste des membres
                    </button>
            </div>

                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#liste-membres">Liste des Membres</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ajouter-membre">
                                    <?php echo $membre_a_modifier ? 'Modifier Membre' : 'Ajouter un Membre'; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="liste-membres">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Nom</th>
                                                <th>Téléphone</th>
                                                <th>Date d'Entrée</th>
                                                <th>Plan (G)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($membres as $membre): ?>
                                            <tr>
                                                <td><?php echo $membre['code_membre']; ?></td>
                                                <td><?php echo $membre['nom'] . ' ' . $membre['prenom']; ?></td>
                                                <td><?php echo $membre['telephone']; ?></td>
                                                <td><?php echo $membre['date_entree']; ?></td>
                                                <td><?php echo number_format($membre['plan'], 2); ?> GDES</td>
                                                
                                                <td>
                                                    <div class="btn-actions">
                                                        <a href="membre_details.php?id=<?php echo $membre['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="?edit_id=<?php echo $membre['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete_id=<?php echo $membre['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="ajouter-membre">
                                <form method="POST">
                                    <?php if ($membre_a_modifier): ?>
                                        <input type="hidden" name="id" value="<?php echo $membre_a_modifier['id']; ?>">
                                    <?php endif; ?>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nom" class="form-label">Nom</label>
                                                <input type="text" class="form-control" id="nom" name="nom" 
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['nom'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="prenom" class="form-label">Prénom</label>
                                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['prenom'] : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Téléphone</label>
                                                <input type="text" class="form-control" id="telephone" name="telephone"
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['telephone'] : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['email'] : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="adresse" class="form-label">Adresse</label>
                                                <input type="text" class="form-control" id="adresse" name="adresse"
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['adresse'] : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sexe" class="form-label">Sexe</label>
                                                <select class="form-select" id="sexe" name="sexe">
                                                    <option value="M" <?php echo ($membre_a_modifier && $membre_a_modifier['sexe'] == 'M') ? 'selected' : ''; ?>>Masculin</option>
                                                    <option value="F" <?php echo ($membre_a_modifier && $membre_a_modifier['sexe'] == 'F') ? 'selected' : ''; ?>>Féminin</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="date_entree" class="form-label">Date d'Entrée</label>
                                                <input type="date" class="form-control" id="date_entree" name="date_entree" 
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['date_entree'] : date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="plan" class="form-label">Plan (Gourdes)</label>
                                                <input type="number" class="form-control" id="plan" name="plan" min="0" step="100" 
                                                       value="<?php echo $membre_a_modifier ? $membre_a_modifier['plan'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <a href="membres.php" class="btn btn-secondary me-2">Annuler</a>
                                        <?php if ($membre_a_modifier): ?>
                                            <button type="submit" name="modifier_membre" class="btn btn-warning">Modifier Membre</button>
                                        <?php else: ?>
                                            <button type="submit" name="ajouter_membre" class="btn btn-finex">Enregistrer Membre</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activer l'onglet d'ajout/modification si on est en mode édition
        <?php if ($membre_a_modifier): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var tab = new bootstrap.Tab(document.querySelector('a[href="#ajouter-membre"]'));
                tab.show();
            });
        <?php endif; ?>

        function imprimerMembres() {
    // Créer une nouvelle fenêtre pour l'impression
    let fenetreImpression = window.open('', '_blank');
    
    // Contenu HTML pour l'impression
    let contenuImpression = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Liste des Membres</title>
            <style>
                @page {
                    margin: 15mm;
                }
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0;
                    padding: 0;
                    font-size: 12px;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #000;
                }
                .logo-container {
                    text-align: center;
                    margin-bottom: 10px;
                }
                .logo {
                    max-height: 40px;
                    width: auto;
                }
                .sol-name {
                    font-size: 35px;
                    font-weight: bold;
                    color: #005653;
                    text-transform: uppercase;
                    margin: 10px 0 5px 0;
                }
                .contact-info {
                    font-size: 11px;
                    margin: 3px 0;
                }
                .report-title {
                    font-size: 18px;
                    font-weight: bold;
                    margin: 15px 0;
                    text-decoration: underline;
                }
                .summary {
                    margin: 15px 0;
                    padding: 15px;
                    background-color: #f9f9f9;
                    font-size: 13px;
                    page-break-inside: avoid;
                }
                .summary-item {
                    display: flex;
                    justify-content: space-between;
                    margin: 5px 0;
                }
                .summary-label {
                    font-weight: bold;
                }
                
                /* Styles pour le tableau des membres */
                .membres-table-container {
                    margin: 25px 0;
                    page-break-inside: avoid;
                }
                .membres-table-container h3 {
                    text-align: center;
                    font-size: 16px;
                    margin-bottom: 15px;
                    text-decoration: underline;
                    color: #041e64ff;
                }
                .membres-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0 auto;
                    font-size: 11px;
                }
                .membres-table th {
                    background-color: #041e64ff;
                    color: white;
                    padding: 10px 5px;
                    text-align: center;
                    font-weight: bold;
                    border: 1px solid #ddd;
                    font-size: 12px;
                }
                .membres-table td {
                    padding: 8px 5px;
                    border: 1px solid #ddd;
                    text-align: center;
                    vertical-align: middle;
                }
                .membres-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .membres-table tr:hover {
                    background-color: #f5f5f5;
                }
                
                /* Colonnes spécifiques */
                .col-num {
                    width: 40px;
                    font-weight: bold;
                }
                .col-code {
                    width: 100px;
                    font-weight: bold;
                    color: #041e64ff;
                }
                .col-nom {
                    width: 150px;
                    text-align: left;
                }
                .col-prenom {
                    width: 150px;
                    text-align: left;
                }
                .col-telephone {
                    width: 120px;
                }
                .col-email {
                    width: 180px;
                    text-align: left;
                }
                .col-date {
                    width: 100px;
                }
                .col-plan {
                    width: 100px;
                    font-weight: bold;
                }
                
                /* Pagination pour impression */
                .page-break {
                    page-break-before: always;
                }
                
                .footer { 
                    margin-top: 30px; 
                    text-align: center; 
                    font-size: 10px;
                    color: #666;
                    border-top: 1px solid #ccc;
                    padding-top: 10px;
                    page-break-inside: avoid;
                }
                @media print {
                    .membres-table th {
                        background-color: #005653 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-container">
                    <img src="Assets/images/declinaison 1 muso sans fond.png" alt="Logo MUSO" class="logo">
                </div>
                <div class="sol-name"><?php echo $utilisateur['nom_sol'] ?? ''; ?></div>
                
                <div class="report-title">
                    LISTE DES MEMBRES
                </div>
            </div>
            
            <?php 
            // Obtenir les données des membres (vous devez ajuster selon votre structure)
            // Assurez-vous que $membres existe avec les bonnes données
            $totalMembres = count($membres ?? []);
            $dateGeneration = date('d/m/Y à H:i');
            
            // Calculer les statistiques par plan
            
            ?>
            
            <div class="summary">
                <div class="summary-item">
                    <span class="summary-label">Date de génération:</span>
                    <span><?php echo date('d/m/Y'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Nombre total de membres:</span>
                    <span><?php echo $totalMembres; ?></span>
                </div>
                
            </div>
            
            <div class="membres-table-container">
                <h3>DÉTAIL DES MEMBRES</h3>
                
                <?php if ($totalMembres > 0): ?>
                    <table class="membres-table">
                        <thead>
                            <tr>
                                <th class="col-num">N°</th>
                                <th class="col-code">Code Membre</th>
                                <th class="col-nom">Nom</th>
                                <th class="col-prenom">Prénom</th>
                                <th class="col-telephone">Téléphone</th>
                                <th class="col-email">Email</th>
                                <th class="col-date">Date Entrée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            $itemsPerPage = 40; // Nombre d'éléments par page
                            $pageCount = 0;
                            
                            foreach ($membres as $index => $membre): 
                                // Ajouter un saut de page après 40 éléments
                                if ($counter > 1 && ($counter - 1) % $itemsPerPage === 0) {
                                    echo '</tbody></table>';
                                    echo '</div><div class="page-break"></div>';
                                    echo '<div class="membres-table-container">';
                                    echo '<table class="membres-table"><thead><tr>';
                                    echo '<th class="col-num">N°</th><th class="col-code">Code Membre</th><th class="col-nom">Nom</th>';
                                    echo '<th class="col-prenom">Prénom</th><th class="col-telephone">Téléphone</th><th class="col-email">Email</th>';
                                   
                                    echo '</tr></thead><tbody>';
                                }
                            ?>
                                <tr>
                                    <td class="col-num"><?php echo $counter; ?></td>
                                    <td class="col-code"><?php echo htmlspecialchars($membre['code_membre'] ?? ''); ?></td>
                                    <td class="col-nom"><?php echo htmlspecialchars($membre['nom'] ?? ''); ?></td>
                                    <td class="col-prenom"><?php echo htmlspecialchars($membre['prenom'] ?? ''); ?></td>
                                    <td class="col-telephone"><?php echo htmlspecialchars($membre['telephone'] ?? 'Non renseigné'); ?></td>
                                    <td class="col-email"><?php echo htmlspecialchars($membre['email'] ?? 'Non renseigné'); ?></td>
                                    <td class="col-date"><?php echo !empty($membre['date_entree']) ? date('d/m/Y', strtotime($membre['date_entree'])) : ''; ?></td>
                                    
                                </tr>
                            <?php 
                                $counter++;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; font-style: italic; color: #666;">
                        Aucun membre trouvé.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="footer">
                <p>Document généré le <?php echo $dateGeneration; ?> - MUSO </p>
                <p>Page 1 sur <?php echo ceil($totalMembres / $itemsPerPage); ?></p>
            </div>
        </body>
        </html>
    `;
    
    // Écrire le contenu dans la nouvelle fenêtre et lancer l'impression
    fenetreImpression.document.write(contenuImpression);
    fenetreImpression.document.close();
    fenetreImpression.focus();
    
    // Attendre que le contenu soit chargé avant d'imprimer
    fenetreImpression.onload = function() {
        fenetreImpression.print();
        // fenetreImpression.close(); // Optionnel: fermer la fenêtre après impression
    };
}
    </script>
</body>
</html>
<?php include 'footer.php'; ?>
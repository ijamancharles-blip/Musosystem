<?php
include 'config.php';
redirectIfNotLogged();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM utilisateurs WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    echo "<div class='alert alert-danger'>Utilisateur non trouvé</div>";
    exit;
}

// Fonction pour sécuriser l'affichage des données
function secureDisplay($data) {
    if ($data === null || $data === '') {
        return '';
    }
    return htmlspecialchars($data);
}

// Initialiser les variables avec des valeurs par défaut
$user_nom = secureDisplay($user['nom'] ?? '');
$user_prenom = secureDisplay($user['prenom'] ?? '');
$user_email = secureDisplay($user['email'] ?? '');
$user_telephone = secureDisplay($user['telephone'] ?? '');
$user_adresse = secureDisplay($user['adresse'] ?? '');
$nom_Sol=secureDisplay($user['nom_sol']?? '');
$user_date_creation = $user['date_creation'] ?? date('Y-m-d H:i:s');

// Mettre à jour le profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $nom_sol=trim($_POST['nom_sol']);
    
    // Validation des champs
    if(empty($nom) || empty($prenom) || empty($email)) {
        $error_profile = "Les champs nom, prénom et email sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_profile = "L'adresse email n'est pas valide";
    } else {
        // Vérifier si l'email existe déjà pour un autre utilisateur
        $query = "SELECT id FROM utilisateurs WHERE email = :email AND id != :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $error_profile = "Cet email est déjà utilisé par un autre utilisateur";
        } else {
            $query = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, 
                      telephone = :telephone, nom_sol=:nom_sol, adresse = :adresse WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':nom_sol', $nom_sol);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                // Mettre à jour la session
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['user_email'] = $email;
                
                $success_profile = "Profil mis à jour avec succès!";
                
                // Recharger les données utilisateur
                $query = "SELECT * FROM utilisateurs WHERE id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Mettre à jour les variables d'affichage
                $user_nom = secureDisplay($user['nom'] ?? '');
                $user_prenom = secureDisplay($user['prenom'] ?? '');
                $user_email = secureDisplay($user['email'] ?? '');
                $user_telephone = secureDisplay($user['telephone'] ?? '');
                $user_adresse = secureDisplay($user['adresse'] ?? '');
                $user_nomsol = secureDisplay($user['nom_sol'] ?? '');
                $user_date_creation = $user['date_creation'] ?? date('Y-m-d H:i:s');
            } else {
                $error_profile = "Erreur lors de la mise à jour du profil";
            }
        }
    }
}

// Changer le mot de passe
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation des mots de passe
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_password = "Tous les champs sont obligatoires";
    } elseif ($new_password !== $confirm_password) {
        $error_password = "Les nouveaux mots de passe ne correspondent pas";
    } elseif (strlen($new_password) < 6) {
        $error_password = "Le nouveau mot de passe doit contenir au moins 6 caractères";
    } elseif (!password_verify($current_password, $user['mot_de_passe'])) {
        $error_password = "Le mot de passe actuel est incorrect";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':mot_de_passe', $hashed_password);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $success_password = "Mot de passe changé avec succès!";
        } else {
            $error_password = "Erreur lors du changement de mot de passe";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO - Profil</title>
    <link rel="icon" href="./Assets/images/mus" type="image/x-icon">
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
            background-color: #041e64ff;
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
        .btn-finex {
            background-color: #3498db;
            color: white;
            border: none;
        }
        .btn-finex:hover {
            background-color: #2980b9;
            color: white;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 3px solid #3498db;
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
                <h2 class="mb-4">Profil Utilisateur</h2>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <img src="assets/images/declinaison 2 muso.png" class="rounded-circle profile-img mb-3" alt="Photo de profil" onerror="this.src='https://via.placeholder.com/120?text=PROFIL'">
                                <h5><?php echo $user_prenom . ' ' . $user_nom; ?></h5>
                                <p class="text-muted"><?php echo $user_email; ?></p>
                                <p class="text-muted">Membre depuis <?php echo date('d/m/Y', strtotime($user_date_creation)); ?></p>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#changePhotoModal">Changer Photo</button>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-chart-line me-2"></i>Statistiques du Compte
                            </div>
                            <div class="card-body">
                                <?php
                                // Compter les membres
                                $query = "SELECT COUNT(*) as total FROM membres WHERE utilisateur_id = :user_id";
                                $stmt = $conn->prepare($query);
                                $stmt->bindParam(':user_id', $user_id);
                                $stmt->execute();
                                $total_membres = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                
                                // Compter les transactions
                                $query = "SELECT COUNT(*) as total FROM transactions WHERE utilisateur_id = :user_id";
                                $stmt = $conn->prepare($query);
                                $stmt->bindParam(':user_id', $user_id);
                                $stmt->execute();
                                $total_transactions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                                ?>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Membres:</span>
                                    <strong><?php echo $total_membres; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Transactions:</span>
                                    <strong><?php echo $total_transactions; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Compte créé:</span>
                                    <strong><?php echo date('d/m/Y', strtotime($user_date_creation)); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-user-edit me-2"></i>Modifier le Profil
                            </div>
                            <div class="card-body">
                                <?php if (isset($success_profile)): ?>
                                    <div class="alert alert-success"><?php echo $success_profile; ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($error_profile)): ?>
                                    <div class="alert alert-danger"><?php echo $error_profile; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $user_nom; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $user_prenom; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user_email; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">Téléphone</label>
                                                <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo $user_telephone; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Nom du Mutuelle</label>
                                        <textarea class="form-control" id="nom_sol" name="nom_sol" ><?php echo $nom_sol; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="3"><?php echo $user_adresse; ?></textarea>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-finex">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="fas fa-lock me-2"></i>Changer le Mot de Passe
                            </div>
                            <div class="card-body">
                                <?php if (isset($success_password)): ?>
                                    <div class="alert alert-success"><?php echo $success_password; ?></div>
                                <?php endif; ?>
                                
                                <?php if (isset($error_password)): ?>
                                    <div class="alert alert-danger"><?php echo $error_password; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Mot de Passe Actuel <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nouveau Mot de Passe <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        <div class="form-text">Le mot de passe doit contenir au moins 6 caractères</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le Nouveau Mot de Passe <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-finex">
                                        <i class="fas fa-key me-2"></i>Changer le Mot de Passe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour changer la photo -->
    <div class="modal fade" id="changePhotoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Changer la photo de profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Fonctionnalité à venir...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation côté client
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let valid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            valid = false;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Veuillez remplir tous les champs obligatoires');
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>
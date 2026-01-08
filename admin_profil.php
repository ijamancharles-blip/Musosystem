<?php
include 'config.php';
redirectIfNotLogged();
redirectIfNotAdmin();

$db = new Database();
$conn = $db->getConnection();
$user_id = getUserId();

// Récupérer les informations de l'utilisateur admin
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
$user_date_creation = $user['date_creation'] ?? date('Y-m-d H:i:s');

// Mettre à jour le profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    
    // Validation des champs
    if (empty($nom) || empty($prenom) || empty($email)) {
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
                      telephone = :telephone, adresse = :adresse WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
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
    <title>FINEX SYSTEM - Profil Administrateur</title>
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
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-admin {
            background: linear-gradient(135deg, #041e64ff 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .main-content {
            padding: 30px;
            margin-left: 0;
        }
        
        .page-title {
            color: #041e64ff;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #041e64ff 0%, #3498db 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            border: none;
        }
        
        .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #041e64ff 0%, #3498db 100%);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(4, 30, 100, 0.3);
            color: white;
        }
        
        .btn-return {
            background: #6c757d;
            border: none;
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-return:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }
        
        .alert-finex {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .avatar-lg {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, #041e64ff 0%, #3498db 100%);
            margin: 0 auto 20px;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #3498db;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .stat-badge {
            font-size: 0.8rem;
            padding: 8px 12px;
            border-radius: 20px;
            margin: 2px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-admin navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-crown me-2"></i>FINEX ADMIN
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="avatar-sm bg-warning me-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span>Administrateur</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_profil.php">
                            <i class="fas fa-user me-2"></i>Mon Profil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <!-- Bouton Retour -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="admin_dashboard.php" class="btn btn-return">
                    <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
                </a>
            </div>
            <h2 class="page-title">
                <i class="fas fa-user-shield me-2"></i>Profil Administrateur
            </h2>
            <div class="badge bg-warning text-dark fs-6 px-3 py-2">
                <i class="fas fa-shield-alt me-1"></i>Super Administrateur
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Profil Administrateur</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="avatar-lg">
                            <?php echo strtoupper(substr($user_prenom, 0, 1) . substr($user_nom, 0, 1)); ?>
                        </div>
                        <h4><?php echo $user_prenom . ' ' . $user_nom; ?></h4>
                        <p class="text-muted"><?php echo $user_email; ?></p>
                        <p class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Membre depuis <?php echo date('d/m/Y', strtotime($user_date_creation)); ?>
                        </p>
                        
                        <div class="mt-4">
                            <h6>Statut du Compte</h6>
                            <span class="badge bg-success stat-badge">
                                <i class="fas fa-check me-1"></i>Compte Actif
                            </span>
                            <span class="badge bg-warning text-dark stat-badge">
                                <i class="fas fa-crown me-1"></i>Administrateur
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations du Compte
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong><i class="fas fa-envelope me-2 text-primary"></i>Email:</strong>
                            <p class="mb-1"><?php echo $user_email; ?></p>
                        </div>
                        <?php if ($user_telephone): ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-phone me-2 text-primary"></i>Téléphone:</strong>
                            <p class="mb-1"><?php echo $user_telephone; ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($user_adresse): ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse:</strong>
                            <p class="mb-1"><?php echo $user_adresse; ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <strong><i class="fas fa-calendar me-2 text-primary"></i>Date de création:</strong>
                            <p class="mb-1"><?php echo date('d/m/Y à H:i', strtotime($user_date_creation)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Modifier le Profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_profile)): ?>
                            <div class="alert alert-success alert-finex">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success_profile; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_profile)): ?>
                            <div class="alert alert-danger alert-finex">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_profile; ?>
                            </div>
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
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control" id="adresse" name="adresse" rows="3"><?php echo $user_adresse; ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-admin">
                                <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2"></i>Changer le Mot de Passe
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_password)): ?>
                            <div class="alert alert-success alert-finex">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success_password; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_password)): ?>
                            <div class="alert alert-danger alert-finex">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_password; ?>
                            </div>
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
                            <button type="submit" name="change_password" class="btn btn-admin">
                                <i class="fas fa-key me-2"></i>Changer le Mot de Passe
                            </button>
                        </form>
                    </div>
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
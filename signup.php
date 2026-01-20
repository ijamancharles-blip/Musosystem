<?php
include 'config.php';

// Vérifier si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$error_type = '';
$form_data = [
    'nom' => '',
    'prenom' => '',
    'nom_sol' => '',
    'email' => '',
    'telephone' => '',
    'adresse' => '',
    'sexe' => 'M'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer et nettoyer les données
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $nom_sol = trim($_POST['nom_sol']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $sexe = $_POST['sexe'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Sauvegarder les données pour réaffichage
    $form_data = compact('nom', 'prenom', 'nom_sol', 'email', 'telephone', 'adresse', 'sexe');
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($nom_sol) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs obligatoires";
        $error_type = 'champs_vides';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide";
        $error_type = 'email_invalide';
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
        $error_type = 'password_trop_court';
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
        $error_type = 'password_mismatch';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Vérifier si l'email existe déjà
        $check_query = "SELECT id FROM utilisateurs WHERE email = :email";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Cet email est déjà utilisé. Veuillez utiliser une autre adresse email.";
            $error_type = 'email_existe';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO utilisateurs (nom, prenom, nom_sol, email, telephone, adresse, sexe, mot_de_passe) 
                     VALUES (:nom, :prenom, :nom_sol, :email, :telephone, :adresse, :sexe, :mot_de_passe)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom_sol', $nom_sol);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':sexe', $sexe);
            $stmt->bindParam(':mot_de_passe', $hashed_password);
            
            if ($stmt->execute()) {
                header("Location: login.php?success=inscription_reussie");
                exit();
            } else {
                $error = "Erreur lors de la création du compte. Veuillez réessayer.";
                $error_type = 'erreur_serveur';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO - Inscription</title>
    <link rel="icon" href="./Assets/images/mus" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       :root {
            --primary: #005653;
            --secondary: #01dc82;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #17a2b8;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: 
        linear-gradient(135deg, rgba(0, 86, 83, 0.6), rgba(3, 124, 104, 0.6)),
        url('Assets/images/9.jpg') no-repeat center center fixed;
    background-size: cover;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 700px;
            width: 90%;
            margin: 20px;
        }
        
        .signup-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }
        
        .logo {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        
        .welcome-subtitle {
            color: #666;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        
        .signup-form-container {
            padding: 30px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-signup {
            background: linear-gradient(135deg, var(--success) 0%, #27ae60 100%);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 3;
        }
        
        /* Styles pour les modals d'erreur */
        .modal-error {
            border-radius: 15px;
            border: none;
        }
        
        .modal-error .modal-header {
            border-radius: 15px 15px 0 0;
            border: none;
            color: white;
        }
        
        .modal-error .modal-content {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-danger .modal-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .modal-warning .modal-header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }
        
        .modal-info .modal-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .login-link p {
            margin-bottom: 15px;
            color: #666;
        }
        
        .btn-login {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-login:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }
        
        @media (max-width: 576px) {
            .signup-container {
                width: 95%;
                margin: 10px;
            }
            
            .signup-form-container {
                padding: 20px;
            }
            
            .logo {
                font-size: 1.8rem;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <!-- En-tête -->
        <div class="signup-header">
            <div class="logo">
                <i class="fas fa-money-bill me-2"></i>MUSO
            </div>
            <p class="mb-0">Créez votre compte en quelques secondes</p>
        </div>
        
        <!-- Formulaire -->
        <div class="signup-form-container">
            <h1 class="welcome-title">Inscription</h1>
            <p class="welcome-subtitle">
                Rejoignez MUSO et gérez vos finances efficacement
            </p>
            
            <form method="POST" id="signupForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?php echo htmlspecialchars($form_data['prenom']); ?>" 
                                       placeholder="Votre prénom" required>
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?php echo htmlspecialchars($form_data['nom']); ?>" 
                                       placeholder="Votre nom" required>
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="nom_sol" class="form-label">Nom de la Mutuelle <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="nom_sol" name="nom_sol" 
                               value="<?php echo htmlspecialchars($form_data['nom_sol']); ?>" 
                               placeholder="Nom de votre Mutuelle" required>
                        <span class="input-icon">
                            <i class="fas fa-home"></i>
                        </span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                               placeholder="votre@email.com" required>
                        <span class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($form_data['telephone']); ?>" 
                                       placeholder="Votre téléphone">
                                <span class="input-icon">
                                    <i class="fas fa-phone"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sexe" class="form-label">Sexe</label>
                            <select class="form-select" id="sexe" name="sexe" style="padding: 12px 15px; border-radius: 10px; border: 2px solid #e9ecef;">
                                <option value="M" <?php echo $form_data['sexe'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                                <option value="F" <?php echo $form_data['sexe'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <textarea class="form-control" id="adresse" name="adresse" rows="2" 
                              placeholder="Votre adresse complète"><?php echo htmlspecialchars($form_data['adresse']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="6 caractères minimum" required minlength="6">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmation <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirmez le mot de passe" required>
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            <div class="password-strength" id="passwordMatch"></div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-signup">
                    <i class="fas fa-user-plus me-2"></i>Créer mon compte
                </button>
                
                <div class="login-link">
                    <p>Vous avez déjà un compte ?</p>
                    <a href="login.php" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modals pour les différents types d'erreurs -->
    <?php if (!empty($error)): ?>
        <?php
        // Déterminer le type de modal en fonction du type d'erreur
        $modal_class = 'modal-danger';
        $modal_icon = 'fas fa-exclamation-triangle';
        $modal_title = 'Erreur d\'Inscription';
        
        switch($error_type) {
            case 'champs_vides':
                $modal_class = 'modal-warning';
                $modal_icon = 'fas fa-exclamation-circle';
                $modal_title = 'Champs Manquants';
                break;
            case 'email_invalide':
                $modal_class = 'modal-warning';
                $modal_icon = 'fas fa-envelope';
                $modal_title = 'Email Invalide';
                break;
            case 'password_trop_court':
                $modal_class = 'modal-warning';
                $modal_icon = 'fas fa-lock';
                $modal_title = 'Mot de Passe Faible';
                break;
            case 'password_mismatch':
                $modal_class = 'modal-warning';
                $modal_icon = 'fas fa-unlock-alt';
                $modal_title = 'Mots de Passe Différents';
                break;
            case 'email_existe':
                $modal_class = 'modal-info';
                $modal_icon = 'fas fa-user-times';
                $modal_title = 'Email Déjà Utilisé';
                break;
            case 'erreur_serveur':
                $modal_class = 'modal-danger';
                $modal_icon = 'fas fa-server';
                $modal_title = 'Erreur Serveur';
                break;
        }
        ?>
        
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-error <?php echo $modal_class; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">
                            <i class="<?php echo $modal_icon; ?> me-2"></i><?php echo $modal_title; ?>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="<?php echo $modal_icon; ?> fa-3x 
                                <?php echo $modal_class == 'modal-danger' ? 'text-danger' : 
                                        ($modal_class == 'modal-warning' ? 'text-warning' : 'text-info'); ?>">
                            </i>
                        </div>
                        <h4 class="mb-3 
                            <?php echo $modal_class == 'modal-danger' ? 'text-danger' : 
                                    ($modal_class == 'modal-warning' ? 'text-warning' : 'text-info'); ?>">
                            <?php echo $modal_title; ?>
                        </h4>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                        
                        <?php if ($error_type == 'email_existe'): ?>
                            <div class="mt-3">
                                <a href="login.php" class="btn 
                                    <?php echo $modal_class == 'modal-danger' ? 'btn-danger' : 
                                            ($modal_class == 'modal-warning' ? 'btn-warning' : 'btn-info'); ?>">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn 
                            <?php echo $modal_class == 'modal-danger' ? 'btn-danger' : 
                                    ($modal_class == 'modal-warning' ? 'btn-warning' : 'btn-info'); ?>" 
                                data-bs-dismiss="modal">
                            <i class="fas fa-redo me-2"></i>Réessayer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher automatiquement le modal d'erreur s'il y a une erreur
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($error)): ?>
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
            <?php endif; ?>
            
            // Validation du mot de passe en temps réel
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordMatch = document.getElementById('passwordMatch');
            
            password.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                passwordStrength.innerHTML = strength.text;
                passwordStrength.className = 'password-strength ' + strength.class;
            });
            
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    passwordMatch.innerHTML = '<span class="text-danger">Les mots de passe ne correspondent pas</span>';
                } else {
                    passwordMatch.innerHTML = '<span class="text-success">Les mots de passe correspondent</span>';
                }
            });
            
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = '';
                let className = '';
                
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                switch(strength) {
                    case 0:
                    case 1:
                        feedback = 'Faible';
                        className = 'strength-weak';
                        break;
                    case 2:
                    case 3:
                        feedback = 'Moyen';
                        className = 'strength-medium';
                        break;
                    case 4:
                    case 5:
                        feedback = 'Fort';
                        className = 'strength-strong';
                        break;
                }
                
                return { text: feedback, class: className };
            }
            
            // Validation du formulaire
            const signupForm = document.getElementById('signupForm');
            signupForm.addEventListener('submit', function(e) {
                const requiredFields = signupForm.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (password.value !== confirmPassword.value) {
                    valid = false;
                    password.classList.add('is-invalid');
                    confirmPassword.classList.add('is-invalid');
                }
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
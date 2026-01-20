<?php
// login.php
ob_start(); // Anpeche output anvan header

// Demare sesyon
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

// Fonksyon lokal si pa defini nan config.php
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }
}

// Si itilizatè deja konekte, redirije li
if (isLoggedIn()) {
    // Tcheke si kont aktif
    if (isset($_SESSION['is_active']) && $_SESSION['is_active'] == 0) {
        header('Location: abonnement.php');
        exit;
    }
    
    // Redirije selon wòl
    if (isAdmin()) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires";
    } else {
        $query = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
    $error = "Aucun compte trouvé avec cet email";
} elseif ($user['is_active'] == 0) {
    // Kont sispann - kreye sesyon epi voye nan paj kont sispann
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_prenom'] = $user['prenom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['is_active'] = $user['is_active'];
    $_SESSION['logged_in'] = true;
    $_SESSION['error'] = "Votre compte a été suspendu. Veuillez contacter l'administrateur.";
    
    header("Location: abonnement.php");
    exit;
        } elseif (!password_verify($password, $user['mot_de_passe'])) {
            $error = "Mot de passe incorrect. Vérifiez votre saisie.";
        } else {
            // Konèksyon reyisi
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['is_active'] = $user['is_active'];
            $_SESSION['logged_in'] = true;
            
            // Mete ajou dènye koneksyon
            $update_query = "UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = :id";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $update_stmt->execute();
            
            // Redireksyon selon kalite itilizatè
            if ($user['is_admin'] == 1) {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        }
    }
}

ob_end_flush(); // Libere buffer
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUSO - Connexion</title>
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
                linear-gradient(135deg, rgba(0, 86, 83, 0.6), rgba(255, 255, 255, 0.6)),
                url('Assets/images/9.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-left {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 600px;
        }
        
        .login-left-content {
            text-align: center;
            padding: 40px;
            z-index: 2;
            position: relative;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            z-index: 1;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }
        
        .slogan {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .features-list {
            text-align: left;
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .feature-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .login-right {
            padding: 20px;
            display: flex;
            align-items: center;
            min-height: 600px;
        }
        
        .login-form-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .welcome-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .welcome-subtitle {
            color: #666;
            margin-bottom: 20px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(4, 30, 100, 0.3);
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
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
        
        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .register-link p {
            margin-bottom: 15px;
            color: #666;
        }
        
        .btn-register {
            background: transparent;
            border: 2px solid var(--success);
            color: var(--success);
            border-radius: 20px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            background: var(--success);
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .login-left {
                display: none;
            }
            
            .login-right {
                padding: 40px 30px;
            }
        }
        
        /* Animation pour les messages d'erreur */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="row g-0">
                <!-- Section gauche avec image -->
                <div class="col-lg-6">
                    <div class="login-left">
                        <div class="login-left-content">
                            <div class="logo">
                                <i class="fas fa-money-bill me-2"></i>MUSO SYSTEM
                            </div>
                            <p class="slogan">
                                Votre partenaire de confiance pour la gestion financière Mutuelle
                            </p>
                            
                            <div class="features-list">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <strong>Sécurité Maximale</strong><br>
                                        <small>Vos données sont protégées</small>
                                    </div>
                                </div>
                                
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div>
                                        <strong>Analyses Avancées</strong><br>
                                        <small>Suivez vos performances</small>
                                    </div>
                                </div>
                                
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div>
                                        <strong>Interface Rapide</strong><br>
                                        <small>Expérience utilisateur fluide</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Section droite avec formulaire -->
                <div class="col-lg-6">
                    <div class="login-right">
                        <div class="login-form-container">
                            <h1 class="welcome-title">Content de vous revoir</h1>
                            <p class="welcome-subtitle">
                                Connectez-vous à votre compte pour continuer
                            </p>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show shake" id="errorAlert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="loginForm" novalidate>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Adresse Email</label>
                                    <div class="input-group">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($email); ?>" 
                                               placeholder="votre@email.com" required>
                                        <span class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer une adresse email valide.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de Passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Votre mot de passe" required minlength="6">
                                        <span class="input-icon">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Le mot de passe doit contenir au moins 6 caractères.
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="showPassword">
                                    <label class="form-check-label" for="showPassword">
                                        Afficher le mot de passe
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-login" id="submitBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" id="loadingSpinner"></span>
                                </button>
                                
                                <div class="register-link">
                                    <p>Vous n'avez pas de compte ?</p>
                                    <a href="signup.php" class="btn btn-register">
                                        <i class="fas fa-user-plus me-2"></i>Créer un compte
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Afficher/Masquer mot de passe
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });
        
        // Validation côté client
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('loadingSpinner');
            let valid = true;
            
            // Reset styles
            email.classList.remove('is-invalid');
            password.classList.remove('is-invalid');
            
            // Validation email
            if (!email.value || !email.validity.valid) {
                email.classList.add('is-invalid');
                valid = false;
            }
            
            // Validation password
            if (!password.value || password.value.length < 6) {
                password.classList.add('is-invalid');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                return;
            }
            
            // Afficher spinner et désactiver bouton
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
        });
        
        // Auto-focus sur email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
            
            // Supprimer le message d'erreur après 5 secondes
            const errorAlert = document.getElementById('errorAlert');
            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.classList.remove('show');
                    setTimeout(() => errorAlert.remove(), 300);
                }, 5000);
            }
        });
        
        // Entrée pour soumettre le formulaire
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.type !== 'textarea') {
                const form = document.getElementById('loginForm');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.click();
                }
            }
        });
    </script>
</body>
</html>
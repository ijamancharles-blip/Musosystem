<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin = false;
$nom_sol = '';


if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->getConnection();
    $query = "SELECT nom_sol, email, is_admin FROM utilisateurs WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $is_admin = $user['is_admin'];
        $nom_sol = $user['nom_sol'] ?? '';

        
        // Stocker aussi dans la session pour usage futur
        $_SESSION['nom_sol'] = $nom_sol;

    }
}

// Récupérer depuis la session si déjà défini
$nom_sol = $_SESSION['nom_sol'] ?? $nom_sol;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINEX SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #005653;
            --secondary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --info: #17a2b8;
            --light: #ecf0f1;
            --dark: #34495e;
        }
        
        /* NAVBAR MOBILE */
        .navbar-mobile {
            display: none;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            padding: 10px 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .hamburger-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 10px;
        }
        
        .mobile-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
            margin-right: 10px;
        }
        
        .mobile-user {
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }
        
        /* SIDEBAR DESKTOP */
        .sidebar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        /* SIDEBAR MOBILE */
        .sidebar-mobile {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary) 100%);
            z-index: 1040;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-mobile.active {
            transform: translateX(0);
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
        }
        
        .overlay.active {
            display: block;
        }
        
        .user-info {
            padding: 15px 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            background: rgba(0, 0, 0, 0.1);
        }
        
        .logo-container {
            padding: 0px;
            margin-bottom: 5px;
        }
        
        .logo-img {
            max-width: 90px;
            height: auto;
            object-fit: cover;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .logo-img:hover {
            transform: scale(1.05);
            border-color: var(--success);
        }
        
        .user-info p {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
            color: white;
        }
        
        .user-info small {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
        }
        
        .sidebar .nav,
        .sidebar-mobile .nav {
            padding: 10px 0;
        }
        
        .sidebar .nav-item,
        .sidebar-mobile .nav-item {
            margin-bottom: 5px;
        }
        
        .sidebar .nav-link,
        .sidebar-mobile .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 15px 25px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover,
        .sidebar-mobile .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--secondary);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active,
        .sidebar-mobile .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--success);
            color: white;
            font-weight: 600;
        }
        
        .sidebar .nav-link i,
        .sidebar-mobile .nav-link i {
            width: 25px;
            margin-right: 12px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .sidebar .nav-link.logout,
        .sidebar-mobile .nav-link.logout {
            color: rgba(255, 255, 255, 0.8);
            border-left: 4px solid transparent;
        }
        
        .sidebar .nav-link.logout:hover,
        .sidebar-mobile .nav-link.logout:hover {
            background: rgba(231, 76, 60, 0.2);
            border-left: 4px solid var(--danger);
            color: white;
        }
        
        /* CONTENU PRINCIPAL */
        .main-content {
            padding: 20px;
        }
        
        /* RESPONSIVE */
        @media (max-width: 767.98px) {
            .navbar-mobile {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .sidebar {
                display: none;
            }
            
            .sidebar-mobile {
                display: block;
            }
            
            .col-md-10 {
                width: 100%;
                margin-left: 0 !important;
            }
            
            .main-content {
                padding-top: 70px; /* Espace pour la navbar mobile */
            }
            
            .logo-img {
                max-width: 60px;
            }
            
            .sidebar .nav-link,
            .sidebar-mobile .nav-link {
                padding: 12px 20px;
            }
        }
        
        /* Pour desktop */
        @media (min-width: 768px) {
            .sidebar-mobile {
                display: none !important;
            }
            
            .overlay {
                display: none !important;
            }
            
            .navbar-mobile {
                display: none !important;
            }
        }
        
        /* Animation pour le menu actif */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Mobile -->
    <div class="navbar-mobile">
        <button class="hamburger-btn" id="hamburgerBtn">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-flex align-items-center">
            <img src="Assets/images/declinaison 2 muso.png" alt="Logo MUSO" class="mobile-logo">
            <span class="mobile-user"><?php echo htmlspecialchars($nom_sol); ?></span>
        </div>
    </div>
    
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Sidebar Mobile -->
    <div class="sidebar-mobile" id="sidebarMobile">
        <div class="user-info">
            <div class="logo-container">
                <img src="Assets/images/declinaison 2 muso.png" alt="Logo MUSO" class="logo-img">
            </div>
            <p class="mb-0"><?php echo htmlspecialchars($nom_sol); ?></p>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'membres.php' ? 'active' : ''; ?>" href="membres.php">
                    <i class="fas fa-users"></i> Membres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'cotisations.php' ? 'active' : ''; ?>" href="cotisations.php">
                    <i class="fas fa-hand-holding-usd"></i> Cotisations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : ''; ?>" href="finance.php">
                    <i class="fas fa-money-bill-wave"></i> Finance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : ''; ?>" href="rapports.php">
                    <i class="fas fa-chart-bar"></i> Rapports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'prets.php' ? 'active' : ''; ?>" href="prets.php">
                    <i class="fas fa-coins"></i> Gestion des Prêts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                    <i class="fas fa-user-cog"></i> Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link logout" href="logout.php" 
                    onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Desktop -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <div class="user-info">
                    <div class="logo-container">
                        <img src="Assets/images/declinaison 2 muso.png" alt="Logo MUSO" class="logo-img">
                    </div>
                    <p class="mb-0"><?php echo htmlspecialchars($nom_sol); ?></p>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'membres.php' ? 'active' : ''; ?>" href="membres.php">
                            <i class="fas fa-users"></i> Membres
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'cotisations.php' ? 'active' : ''; ?>" href="cotisations.php">
                            <i class="fas fa-hand-holding-usd"></i> Cotisations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : ''; ?>" href="finance.php">
                            <i class="fas fa-money-bill-wave"></i> Finance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : ''; ?>" href="rapports.php">
                            <i class="fas fa-chart-bar"></i> Rapports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'prets.php' ? 'active' : ''; ?>" href="prets.php">
                            <i class="fas fa-coins"></i> Gestion des Prêts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                            <i class="fas fa-user-cog"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout" href="logout.php" 
                            onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebarMobile = document.getElementById('sidebarMobile');
            const overlay = document.getElementById('overlay');
            
            function toggleMobileMenu() {
                sidebarMobile.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.style.overflow = sidebarMobile.classList.contains('active') ? 'hidden' : '';
            }
            
            function closeMobileMenu() {
                sidebarMobile.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Ouvrir/fermer le menu mobile
            hamburgerBtn.addEventListener('click', toggleMobileMenu);
            overlay.addEventListener('click', closeMobileMenu);
            
            // Fermer le menu quand on clique sur un lien (mobile seulement)
            const mobileLinks = document.querySelectorAll('.sidebar-mobile .nav-link');
            mobileLinks.forEach(link => {
                link.addEventListener('click', closeMobileMenu);
            });
            
            // Fermer le menu si on redimensionne vers desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    closeMobileMenu();
                }
            });
            
            // Animation des liens (pour desktop)
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(8px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });
            
            // Garder le lien actif décalé
            const activeLink = document.querySelector('.sidebar .nav-link.active');
            if (activeLink) {
                activeLink.style.transform = 'translateX(5px)';
            }
        });
    </script>
</body>
</html>
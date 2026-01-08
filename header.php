<?php
// header.php - En-tête commun pour toutes les pages
if (!isset($title)) {
    $title = "FINEX SYSTEM";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les infos utilisateur
$user_prenom = $_SESSION['user_prenom'] ?? 'Utilisateur';
$user_nom = $_SESSION['user_nom'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2c3e50;
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
        
        .sidebar {
            background-color: var(--primary);
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
            z-index: 1000;
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
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }
        
        .stat-card.membres {
            background: linear-gradient(45deg, #3498db, #2980b9);
        }
        
        .stat-card.capital {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }
        
        .stat-card.depenses {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .stat-card.entrees {
            background: linear-gradient(45deg, #f39c12, #e67e22);
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .btn-finex {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-finex:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .table th {
            background-color: var(--light);
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 10px;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .nav-tabs .nav-link {
            color: var(--dark);
            font-weight: 500;
            border: none;
            padding: 12px 25px;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--secondary);
            font-weight: 600;
            border-bottom: 3px solid var(--secondary);
            background: transparent;
        }
        
        .user-info {
            color: white;
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .cotisation-case {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            line-height: 36px;
            margin: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .cotisation-case.active {
            background-color: var(--success);
            color: white;
            border-color: var(--success);
            transform: scale(1.1);
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--secondary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert-finex {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .navbar-mobile {
            display: none;
            background-color: var(--primary);
            padding: 10px 20px;
        }
        
        @media (max-width: 768px) {
            .navbar-mobile {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .sidebar {
                display: none;
            }
            .sidebar.mobile-open {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1050;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Navigation Mobile -->
    <nav class="navbar-mobile d-md-none">
        <h5 class="text-white mb-0">FINEX SYSTEM</h5>
        <button class="btn btn-outline-light" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Sidebar Mobile -->
    <div class="sidebar d-md-block" id="sidebarMobile">
        <div class="user-info">
            <h5>FINEX SYSTEM</h5>
            <p class="mb-0"><?php echo $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']; ?></p>
            <small><?php echo $_SESSION['user_email']; ?></small>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'membres.php' ? 'active' : ''; ?>" href="membres.php">
                    <i class="fas fa-users"></i> Membres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : ''; ?>" href="finance.php">
                    <i class="fas fa-money-bill-wave"></i> Finance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'prets.php' ? 'active' : ''; ?>" href="prets.php">
                    <i class="fas fa-hand-holding-usd"></i> Prêts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cotisations.php' ? 'active' : ''; ?>" href="cotisations.php">
                    <i class="fas fa-calendar-check"></i> Cotisations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rapports.php' ? 'active' : ''; ?>" href="rapports.php">
                    <i class="fas fa-chart-bar"></i> Rapports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>" href="profil.php">
                    <i class="fas fa-user"></i> Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <script>
        // Gestion du menu mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const sidebarMobile = document.getElementById('sidebarMobile');
            
            if (mobileMenuToggle && sidebarMobile) {
                mobileMenuToggle.addEventListener('click', function() {
                    sidebarMobile.classList.toggle('mobile-open');
                });
            }
            
            // Fermer le menu mobile en cliquant à l'extérieur
            document.addEventListener('click', function(event) {
                if (sidebarMobile && sidebarMobile.classList.contains('mobile-open') && 
                    !sidebarMobile.contains(event.target) && 
                    event.target !== mobileMenuToggle) {
                    sidebarMobile.classList.remove('mobile-open');
                }
            });
        });
        
        // Fonction pour afficher les loaders
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<div class="loading-spinner"></div> Chargement...';
            button.disabled = true;
            return originalText;
        }
        
        function hideLoading(button, originalText) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>